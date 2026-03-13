<?php

namespace App\Services;

use App\Models\CorporateEmailAccount;
use App\Models\CorporateEmailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class CorporateEmailService
{
    /**
     * Prueba conectividad IMAP y SMTP para una cuenta.
     */
    public function testConnections(CorporateEmailAccount $account): array
    {
        return [
            'imap' => $this->testImapConnection($account),
            'smtp' => $this->testSmtpConnection($account),
        ];
    }

    /**
     * Prueba conexion IMAP.
     */
    public function testImapConnection(CorporateEmailAccount $account): array
    {
        if (!function_exists('imap_open')) {
            return [
                'success' => false,
                'message' => 'La extension IMAP de PHP no esta habilitada en el servidor.',
            ];
        }

        if (!$account->isConfiguredForSync()) {
            return [
                'success' => false,
                'message' => 'La cuenta no tiene configuracion IMAP completa.',
            ];
        }

        $mailbox = $this->buildImapMailbox($account, 'INBOX');
        $imap = @imap_open(
            $mailbox,
            $account->getImapUsernameForAuth(),
            (string) $account->imap_password_decrypted,
            defined('OP_HALFOPEN') ? OP_HALFOPEN : 0,
            1
        );

        if (!$imap) {
            return [
                'success' => false,
                'message' => $this->lastImapError(),
            ];
        }

        @imap_close($imap);

        return [
            'success' => true,
            'message' => 'Conexion IMAP correcta.',
        ];
    }

    /**
     * Prueba conexion SMTP.
     */
    public function testSmtpConnection(CorporateEmailAccount $account): array
    {
        if (!$account->isConfiguredForSend()) {
            return [
                'success' => false,
                'message' => 'La cuenta no tiene configuracion SMTP completa.',
            ];
        }

        try {
            $transport = $this->buildTransport($account);

            if (method_exists($transport, 'start')) {
                $transport->start();
            }

            if (method_exists($transport, 'stop')) {
                $transport->stop();
            }

            return [
                'success' => true,
                'message' => 'Conexion SMTP correcta.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error SMTP: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sincroniza correos entrantes desde IMAP a BD.
     */
    public function syncInbox(CorporateEmailAccount $account, int $limit = 50, string $folder = 'INBOX'): array
    {
        if (!function_exists('imap_open')) {
            return [
                'success' => false,
                'message' => 'La extension IMAP de PHP no esta habilitada en el servidor.',
            ];
        }

        if (!$account->isConfiguredForSync()) {
            return [
                'success' => false,
                'message' => 'La cuenta no tiene configuracion IMAP completa.',
            ];
        }

        $limit = max(1, min(200, $limit));
        $mailbox = $this->buildImapMailbox($account, $folder);
        $imap = null;

        try {
            $imap = @imap_open(
                $mailbox,
                $account->getImapUsernameForAuth(),
                (string) $account->imap_password_decrypted,
                0,
                1
            );

            if (!$imap) {
                throw new \RuntimeException($this->lastImapError());
            }

            $uids = @imap_search($imap, 'ALL', defined('SE_UID') ? SE_UID : 0);
            if (!is_array($uids) || empty($uids)) {
                $account->update([
                    'last_sync_at' => now(),
                    'last_sync_status' => 'ok',
                    'last_sync_error' => null,
                ]);

                @imap_close($imap);

                return [
                    'success' => true,
                    'message' => 'No se encontraron mensajes para sincronizar.',
                    'stats' => [
                        'fetched' => 0,
                        'imported' => 0,
                        'skipped' => 0,
                        'errors' => 0,
                    ],
                ];
            }

            rsort($uids, SORT_NUMERIC);
            $uids = array_slice($uids, 0, $limit);

            $imported = 0;
            $skipped = 0;
            $errors = 0;

            foreach ($uids as $uid) {
                $uidString = (string) $uid;

                try {
                    $exists = CorporateEmailMessage::query()
                        ->where('corporate_email_account_id', $account->id)
                        ->where('direction', 'incoming')
                        ->where('external_uid', $uidString)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    $overviewList = @imap_fetch_overview($imap, $uidString, defined('FT_UID') ? FT_UID : 0);
                    $overview = is_array($overviewList) ? ($overviewList[0] ?? null) : null;

                    if (!$overview) {
                        $errors++;
                        continue;
                    }

                    $fromRecipients = $this->parseAddressList($overview->from ?? null);
                    $toRecipients = $this->parseAddressList($overview->to ?? null);

                    $from = $fromRecipients[0] ?? null;
                    $subject = $this->decodeMimeHeader($overview->subject ?? null) ?: '(Sin asunto)';
                    $externalMessageId = trim((string) ($overview->message_id ?? ''), '<>') ?: null;

                    $body = $this->extractBodies($imap, $uidString);
                    $receivedAt = $this->parseDate($overview->date ?? null);

                    $isSeen = isset($overview->seen) && ((int) $overview->seen === 1);

                    CorporateEmailMessage::create([
                        'corporate_email_account_id' => $account->id,
                        'user_id' => $account->user_id,
                        'direction' => 'incoming',
                        'folder' => $folder,
                        'external_uid' => $uidString,
                        'external_message_id' => $externalMessageId,
                        'subject' => $subject,
                        'from_email' => $from['email'] ?? null,
                        'from_name' => $from['name'] ?? null,
                        'to_recipients' => $toRecipients,
                        'body_text' => $body['text'],
                        'body_html' => $body['html'],
                        'raw_headers' => @imap_fetchheader($imap, $uidString, defined('FT_UID') ? FT_UID : 0) ?: null,
                        'status' => $isSeen ? 'read' : 'unread',
                        'received_at' => $receivedAt,
                        'read_at' => $isSeen ? ($receivedAt ?? now()) : null,
                        'meta' => [
                            'imap_uid' => $uidString,
                            'has_attachments' => $body['has_attachments'],
                            'folder' => $folder,
                        ],
                    ]);

                    $imported++;
                } catch (\Throwable $e) {
                    $errors++;
                    Log::warning('Error importando correo IMAP', [
                        'account_id' => $account->id,
                        'uid' => $uidString,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            @imap_close($imap);

            $account->update([
                'last_sync_at' => now(),
                'last_sync_status' => $errors > 0 ? 'warning' : 'ok',
                'last_sync_error' => $errors > 0 ? "Se completaron con {$errors} errores" : null,
            ]);

            return [
                'success' => true,
                'message' => 'Sincronizacion completada.',
                'stats' => [
                    'fetched' => count($uids),
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'errors' => $errors,
                ],
            ];
        } catch (\Throwable $e) {
            if (is_resource($imap) || is_object($imap)) {
                @imap_close($imap);
            }

            $account->update([
                'last_sync_at' => now(),
                'last_sync_status' => 'failed',
                'last_sync_error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'No fue posible sincronizar el inbox: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Envia un correo y guarda registro de salida.
     *
     * @throws \Throwable
     */
    public function sendMessage(CorporateEmailAccount $account, array $payload): CorporateEmailMessage
    {
        if (!$account->isConfiguredForSend()) {
            throw new InvalidArgumentException('La cuenta no tiene configuracion SMTP completa.');
        }

        $toRecipients = $this->normalizeRecipients($payload['to'] ?? null);
        $ccRecipients = $this->normalizeRecipients($payload['cc'] ?? null);
        $bccRecipients = $this->normalizeRecipients($payload['bcc'] ?? null);

        if (empty($toRecipients)) {
            throw new InvalidArgumentException('Debes indicar al menos un destinatario valido.');
        }

        $subject = trim((string) ($payload['subject'] ?? ''));
        if ($subject === '') {
            $subject = '(Sin asunto)';
        }

        $bodyText = isset($payload['body_text']) ? (string) $payload['body_text'] : null;
        $bodyHtml = isset($payload['body_html']) ? (string) $payload['body_html'] : null;

        if (($bodyText === null || $bodyText === '') && ($bodyHtml === null || $bodyHtml === '')) {
            throw new InvalidArgumentException('Debes enviar body_text o body_html.');
        }

        $email = new Email();
        $email->from(new Address($account->email_address, (string) ($account->from_name ?: $account->name)));

        foreach ($toRecipients as $recipient) {
            $email->addTo(new Address($recipient['email'], (string) ($recipient['name'] ?? '')));
        }

        foreach ($ccRecipients as $recipient) {
            $email->addCc(new Address($recipient['email'], (string) ($recipient['name'] ?? '')));
        }

        foreach ($bccRecipients as $recipient) {
            $email->addBcc(new Address($recipient['email'], (string) ($recipient['name'] ?? '')));
        }

        $email->subject($subject);

        if ($bodyHtml !== null && $bodyHtml !== '') {
            $email->html($bodyHtml);
        }

        if ($bodyText !== null && $bodyText !== '') {
            $email->text($bodyText);
        } elseif ($bodyHtml !== null && $bodyHtml !== '') {
            $email->text(trim(strip_tags($bodyHtml)));
        }

        try {
            $mailer = $this->buildMailer($account);
            $mailer->send($email);

            $messageIdHeader = $email->getHeaders()->get('Message-ID');
            $messageId = $messageIdHeader ? trim($messageIdHeader->getBodyAsString(), '<>') : null;

            return CorporateEmailMessage::create([
                'corporate_email_account_id' => $account->id,
                'user_id' => $account->user_id,
                'direction' => 'outgoing',
                'external_message_id' => $messageId,
                'subject' => $subject,
                'from_email' => $account->email_address,
                'from_name' => $account->from_name,
                'to_recipients' => $toRecipients,
                'cc_recipients' => $ccRecipients,
                'bcc_recipients' => $bccRecipients,
                'body_text' => $bodyText,
                'body_html' => $bodyHtml,
                'status' => 'sent',
                'sent_at' => now(),
                'meta' => [
                    'smtp_host' => $account->smtp_host,
                    'smtp_port' => $account->smtp_port,
                ],
            ]);
        } catch (\Throwable $e) {
            CorporateEmailMessage::create([
                'corporate_email_account_id' => $account->id,
                'user_id' => $account->user_id,
                'direction' => 'outgoing',
                'subject' => $subject,
                'from_email' => $account->email_address,
                'from_name' => $account->from_name,
                'to_recipients' => $toRecipients,
                'cc_recipients' => $ccRecipients,
                'bcc_recipients' => $bccRecipients,
                'body_text' => $bodyText,
                'body_html' => $bodyHtml,
                'status' => 'failed',
                'sent_at' => now(),
                'meta' => [
                    'error' => $e->getMessage(),
                    'smtp_host' => $account->smtp_host,
                    'smtp_port' => $account->smtp_port,
                ],
            ]);

            throw $e;
        }
    }

    protected function buildMailer(CorporateEmailAccount $account): Mailer
    {
        return new Mailer($this->buildTransport($account));
    }

    protected function buildTransport(CorporateEmailAccount $account): TransportInterface
    {
        $username = (string) $account->getSmtpUsernameForAuth();
        $password = (string) ($account->smtp_password_decrypted ?? '');
        $host = trim((string) $account->smtp_host);
        $port = (int) ($account->smtp_port ?: 587);

        $encryption = strtolower((string) $account->smtp_encryption);
        if (!in_array($encryption, ['ssl', 'tls', 'none'], true)) {
            $encryption = 'tls';
        }

        $scheme = $encryption === 'ssl' ? 'smtps' : 'smtp';

        $query = [];
        if ($encryption === 'tls') {
            $query['encryption'] = 'tls';
        }
        if (!$account->imap_validate_cert) {
            $query['verify_peer'] = '0';
        }

        $credentials = '';
        if ($username !== '') {
            $credentials = rawurlencode($username);
            if ($password !== '') {
                $credentials .= ':' . rawurlencode($password);
            }
            $credentials .= '@';
        }

        $queryString = http_build_query($query);
        $dsn = sprintf(
            '%s://%s%s:%d%s',
            $scheme,
            $credentials,
            $host,
            $port,
            $queryString !== '' ? ('?' . $queryString) : ''
        );

        return Transport::fromDsn($dsn);
    }

    protected function buildImapMailbox(CorporateEmailAccount $account, string $folder): string
    {
        $encryption = strtolower((string) $account->imap_encryption);
        if (!in_array($encryption, ['ssl', 'tls', 'none'], true)) {
            $encryption = 'ssl';
        }

        $flags = ['/imap'];
        if ($encryption === 'ssl') {
            $flags[] = '/ssl';
        } elseif ($encryption === 'tls') {
            $flags[] = '/tls';
        } else {
            $flags[] = '/notls';
        }

        if (!$account->imap_validate_cert) {
            $flags[] = '/novalidate-cert';
        }

        $host = trim((string) $account->imap_host);
        $port = (int) ($account->imap_port ?: 993);

        return sprintf('{%s:%d%s}%s', $host, $port, implode('', $flags), $folder);
    }

    protected function parseAddressList(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        if (function_exists('imap_rfc822_parse_adrlist')) {
            $addresses = @imap_rfc822_parse_adrlist($raw, 'localhost');
            if (is_array($addresses) && !empty($addresses)) {
                $parsed = [];

                foreach ($addresses as $address) {
                    $mailbox = $address->mailbox ?? null;
                    $host = $address->host ?? null;

                    if (!$mailbox || !$host || $host === '.SYNTAX-ERROR.') {
                        continue;
                    }

                    $email = $mailbox . '@' . $host;
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        continue;
                    }

                    $name = $this->decodeMimeHeader($address->personal ?? null);

                    $parsed[] = [
                        'name' => $name ?: null,
                        'email' => $email,
                    ];
                }

                if (!empty($parsed)) {
                    return $parsed;
                }
            }
        }

        return $this->normalizeRecipients($raw);
    }

    protected function extractBodies(mixed $imap, string $uid): array
    {
        $textBody = null;
        $htmlBody = null;
        $hasAttachments = false;

        $structure = @imap_fetchstructure($imap, $uid, defined('FT_UID') ? FT_UID : 0);

        if ($structure && is_object($structure)) {
            $this->walkStructure($imap, $uid, $structure, '', $textBody, $htmlBody, $hasAttachments);
        }

        if (($textBody === null || trim($textBody) === '') && ($htmlBody === null || trim($htmlBody) === '')) {
            $fallbackBody = @imap_body($imap, $uid, (defined('FT_UID') ? FT_UID : 0) | (defined('FT_PEEK') ? FT_PEEK : 0));
            if (is_string($fallbackBody) && $fallbackBody !== '') {
                $textBody = $this->decodeBodyPart($fallbackBody, (int) ($structure->encoding ?? 0));
            }
        }

        if (($textBody === null || trim($textBody) === '') && $htmlBody !== null) {
            $textBody = trim(strip_tags($htmlBody));
        }

        return [
            'text' => $textBody,
            'html' => $htmlBody,
            'has_attachments' => $hasAttachments,
        ];
    }

    protected function walkStructure(
        mixed $imap,
        string $uid,
        object $structure,
        string $partNumber,
        ?string &$textBody,
        ?string &$htmlBody,
        bool &$hasAttachments
    ): void {
        $disposition = strtoupper((string) ($structure->disposition ?? ''));
        if (in_array($disposition, ['ATTACHMENT', 'INLINE'], true)) {
            $hasAttachments = true;
        }

        if (isset($structure->parts) && is_array($structure->parts) && count($structure->parts) > 0) {
            foreach ($structure->parts as $index => $part) {
                $nextPartNumber = $partNumber === ''
                    ? (string) ($index + 1)
                    : $partNumber . '.' . ($index + 1);

                if (is_object($part)) {
                    $this->walkStructure($imap, $uid, $part, $nextPartNumber, $textBody, $htmlBody, $hasAttachments);
                }
            }

            return;
        }

        $type = (int) ($structure->type ?? 0);
        if ($type !== 0) {
            return;
        }

        $subtype = strtoupper((string) ($structure->subtype ?? 'PLAIN'));

        if ($partNumber === '') {
            $rawBody = @imap_body($imap, $uid, (defined('FT_UID') ? FT_UID : 0) | (defined('FT_PEEK') ? FT_PEEK : 0));
        } else {
            $rawBody = @imap_fetchbody(
                $imap,
                $uid,
                $partNumber,
                (defined('FT_UID') ? FT_UID : 0) | (defined('FT_PEEK') ? FT_PEEK : 0)
            );
        }

        if (!is_string($rawBody) || $rawBody === '') {
            return;
        }

        $decodedBody = $this->decodeBodyPart($rawBody, (int) ($structure->encoding ?? 0));

        if ($subtype === 'PLAIN' && ($textBody === null || trim($textBody) === '')) {
            $textBody = $decodedBody;
            return;
        }

        if ($subtype === 'HTML' && ($htmlBody === null || trim($htmlBody) === '')) {
            $htmlBody = $decodedBody;
        }
    }

    protected function decodeBodyPart(?string $body, int $encoding): string
    {
        if ($body === null) {
            return '';
        }

        $decoded = $body;

        switch ($encoding) {
            case 3:
                $tmp = base64_decode($body, true);
                $decoded = $tmp !== false ? $tmp : $body;
                break;
            case 4:
                $decoded = quoted_printable_decode($body);
                break;
            default:
                $decoded = $body;
                break;
        }

        if (function_exists('mb_detect_encoding') && function_exists('mb_convert_encoding')) {
            $currentEncoding = mb_detect_encoding($decoded, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
            if ($currentEncoding !== false && strtoupper($currentEncoding) !== 'UTF-8') {
                $decoded = mb_convert_encoding($decoded, 'UTF-8', $currentEncoding);
            }
        }

        return trim($decoded);
    }

    protected function decodeMimeHeader(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!function_exists('imap_mime_header_decode')) {
            return $value;
        }

        $elements = @imap_mime_header_decode($value);
        if (!is_array($elements) || empty($elements)) {
            return $value;
        }

        $result = '';

        foreach ($elements as $element) {
            $charset = strtoupper((string) ($element->charset ?? 'UTF-8'));
            $text = (string) ($element->text ?? '');

            if ($text === '') {
                continue;
            }

            if ($charset !== 'DEFAULT' && $charset !== 'UTF-8' && function_exists('mb_convert_encoding')) {
                try {
                    $text = mb_convert_encoding($text, 'UTF-8', $charset);
                } catch (\Throwable $e) {
                    // Keep original chunk.
                }
            }

            $result .= $text;
        }

        return trim($result) !== '' ? trim($result) : $value;
    }

    protected function parseDate(?string $value): ?Carbon
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function normalizeRecipients(mixed $raw): array
    {
        if ($raw === null) {
            return [];
        }

        $items = [];

        if (is_string($raw)) {
            $parts = preg_split('/[,;]+/', $raw) ?: [];
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $items[] = $part;
                }
            }
        } elseif (is_array($raw)) {
            $items = $raw;
        } else {
            return [];
        }

        $normalized = [];
        $seen = [];

        foreach ($items as $item) {
            $recipient = $this->normalizeRecipient($item);
            if (!$recipient) {
                continue;
            }

            $key = strtolower($recipient['email']);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $normalized[] = $recipient;
        }

        return $normalized;
    }

    protected function normalizeRecipient(mixed $recipient): ?array
    {
        if (is_string($recipient)) {
            $value = trim($recipient);
            if ($value === '') {
                return null;
            }

            $name = null;
            $email = $value;

            if (preg_match('/^(.*?)<([^>]+)>$/', $value, $matches)) {
                $name = trim(trim($matches[1]), '"\' ');
                $email = trim($matches[2]);
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return null;
            }

            return [
                'name' => $name !== '' ? $name : null,
                'email' => $email,
            ];
        }

        if (is_array($recipient)) {
            $email = trim((string) ($recipient['email'] ?? ''));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return null;
            }

            $name = trim((string) ($recipient['name'] ?? ''));

            return [
                'name' => $name !== '' ? $name : null,
                'email' => $email,
            ];
        }

        return null;
    }

    protected function lastImapError(): string
    {
        if (!function_exists('imap_last_error')) {
            return 'Error desconocido de IMAP.';
        }

        $error = imap_last_error();
        if (is_string($error) && $error !== '') {
            return $error;
        }

        if (function_exists('imap_errors')) {
            $errors = imap_errors();
            if (is_array($errors) && !empty($errors)) {
                return (string) end($errors);
            }
        }

        return 'Error desconocido de IMAP.';
    }
}
