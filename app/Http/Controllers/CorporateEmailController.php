<?php

namespace App\Http\Controllers;

use App\Models\CorporateEmailAccount;
use App\Models\CorporateEmailMessage;
use App\Services\CorporateEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class CorporateEmailController extends Controller
{
    protected CorporateEmailService $emailService;

    public function __construct(CorporateEmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * GET /api/corporate-email/accounts
     */
    public function accountsIndex(Request $request): JsonResponse
    {
        $query = CorporateEmailAccount::query()->with('user:id,name,email');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email_address', 'like', "%{$search}%")
                    ->orWhere('from_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $validOrderBy = ['id', 'name', 'email_address', 'created_at', 'updated_at', 'last_sync_at'];
        $orderBy = (string) $request->input('order', 'created_at');
        if (!in_array($orderBy, $validOrderBy, true)) {
            $orderBy = 'created_at';
        }

        $sort = $request->input('sort', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($orderBy, $sort);

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        return $this->apiSuccess(
            'Listado de cuentas de correo corporativo',
            'CORP_EMAIL_ACCOUNTS_LIST',
            $query->paginate($perPage)
        );
    }

    /**
     * POST /api/corporate-email/accounts
     */
    public function storeAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->accountRules(false));
        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $data['imap_validate_cert'] = (bool) ($data['imap_validate_cert'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $account = CorporateEmailAccount::create($data)->load('user:id,name,email');

        return $this->apiCreated(
            'Cuenta de correo creada',
            'CORP_EMAIL_ACCOUNT_CREATED',
            $account
        );
    }

    /**
     * PUT /api/corporate-email/accounts/{account}
     */
    public function updateAccount(Request $request, CorporateEmailAccount $account): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->accountRules(true));
        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        // If password fields are not present, keep existing values.
        if (!$request->has('imap_password')) {
            unset($data['imap_password']);
        }
        if (!$request->has('smtp_password')) {
            unset($data['smtp_password']);
        }

        if (array_key_exists('imap_validate_cert', $data)) {
            $data['imap_validate_cert'] = (bool) $data['imap_validate_cert'];
        }
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $account->update($data);

        return $this->apiSuccess(
            'Cuenta de correo actualizada',
            'CORP_EMAIL_ACCOUNT_UPDATED',
            $account->fresh()->load('user:id,name,email')
        );
    }

    /**
     * DELETE /api/corporate-email/accounts/{account}
     */
    public function destroyAccount(Request $request, CorporateEmailAccount $account): JsonResponse
    {
        $account->delete();

        return $this->apiSuccess(
            'Cuenta de correo eliminada',
            'CORP_EMAIL_ACCOUNT_DELETED',
            null
        );
    }

    /**
     * POST /api/corporate-email/accounts/{account}/test-connection
     */
    public function testConnection(Request $request, CorporateEmailAccount $account): JsonResponse
    {
        $result = $this->emailService->testConnections($account);
        $ok = ($result['imap']['success'] ?? false) && ($result['smtp']['success'] ?? false);

        if (!$ok) {
            return $this->apiError(
                'No fue posible validar todas las conexiones de la cuenta.',
                'CORP_EMAIL_CONNECTION_FAILED',
                $result,
                null,
                422
            );
        }

        return $this->apiSuccess(
            'Conexiones IMAP/SMTP validadas correctamente.',
            'CORP_EMAIL_CONNECTION_OK',
            $result
        );
    }

    /**
     * POST /api/corporate-email/accounts/{account}/sync
     */
    public function syncInbox(Request $request, CorporateEmailAccount $account): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'sometimes|integer|min:1|max:200',
            'folder' => 'sometimes|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $limit = (int) $request->input('limit', 50);
        $folder = (string) $request->input('folder', 'INBOX');

        $result = $this->emailService->syncInbox($account, $limit, $folder);

        if (!($result['success'] ?? false)) {
            return $this->apiError(
                $result['message'] ?? 'No se pudo sincronizar el inbox.',
                'CORP_EMAIL_SYNC_FAILED',
                ['result' => $result],
                null,
                422
            );
        }

        return $this->apiSuccess(
            $result['message'] ?? 'Inbox sincronizado.',
            'CORP_EMAIL_SYNC_OK',
            $result
        );
    }

    /**
     * GET /api/corporate-email/messages
     */
    public function messagesIndex(Request $request): JsonResponse
    {
        $query = CorporateEmailMessage::query()->with([
            'account:id,name,email_address',
            'user:id,name,email',
        ]);

        if ($request->filled('corporate_email_account_id')) {
            $query->where('corporate_email_account_id', (int) $request->input('corporate_email_account_id'));
        }

        if ($request->filled('direction')) {
            $query->where('direction', (string) $request->input('direction'));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('from_email', 'like', "%{$search}%")
                    ->orWhere('from_name', 'like', "%{$search}%");
            });
        }

        $validOrderBy = ['id', 'created_at', 'received_at', 'sent_at', 'subject', 'status'];
        $orderBy = (string) $request->input('order', 'created_at');
        if (!in_array($orderBy, $validOrderBy, true)) {
            $orderBy = 'created_at';
        }

        $sort = $request->input('sort', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($orderBy, $sort);

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        return $this->apiSuccess(
            'Listado de mensajes',
            'CORP_EMAIL_MESSAGES_LIST',
            $query->paginate($perPage)
        );
    }

    /**
     * GET /api/corporate-email/messages/{message}
     */
    public function showMessage(Request $request, CorporateEmailMessage $message): JsonResponse
    {
        if ($message->direction === 'incoming' && $message->status === 'unread') {
            $message->markAsRead();
        }

        return $this->apiSuccess(
            'Mensaje obtenido',
            'CORP_EMAIL_MESSAGE_SHOWN',
            $message->fresh()->load([
                'account:id,name,email_address,from_name',
                'user:id,name,email',
            ])
        );
    }

    /**
     * POST /api/corporate-email/messages/{message}/mark-read
     */
    public function markAsRead(Request $request, CorporateEmailMessage $message): JsonResponse
    {
        $message->markAsRead();

        return $this->apiSuccess(
            'Mensaje marcado como leido',
            'CORP_EMAIL_MESSAGE_MARKED_READ',
            $message->fresh()
        );
    }

    /**
     * POST /api/corporate-email/send
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'corporate_email_account_id' => 'required|exists:corporate_email_accounts,id',
            'to' => 'required',
            'cc' => 'sometimes|nullable',
            'bcc' => 'sometimes|nullable',
            'subject' => 'sometimes|nullable|string|max:255',
            'body_text' => 'sometimes|nullable|string',
            'body_html' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        if (($data['body_text'] ?? null) === null && ($data['body_html'] ?? null) === null) {
            return $this->apiValidationError([
                'body' => ['Debes enviar body_text o body_html.'],
            ]);
        }

        $account = CorporateEmailAccount::find((int) $data['corporate_email_account_id']);
        if (!$account) {
            return $this->apiNotFound('Cuenta de correo no encontrada', 'CORP_EMAIL_ACCOUNT_NOT_FOUND');
        }

        try {
            $message = $this->emailService->sendMessage($account, $data);

            return $this->apiCreated(
                'Correo enviado correctamente',
                'CORP_EMAIL_SENT',
                $message->load(['account:id,name,email_address', 'user:id,name,email'])
            );
        } catch (InvalidArgumentException $e) {
            return $this->apiValidationError([
                'send' => [$e->getMessage()],
            ]);
        } catch (\Throwable $e) {
            return $this->apiError(
                'No fue posible enviar el correo: ' . $e->getMessage(),
                'CORP_EMAIL_SEND_FAILED',
                null,
                null,
                500
            );
        }
    }

    protected function accountRules(bool $isUpdate): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';
        $passwordRequired = $isUpdate ? 'sometimes|nullable' : 'required';

        return [
            'user_id' => $required . '|nullable|exists:users,id',
            'name' => $required . '|string|max:255',
            'email_address' => $required . '|email|max:255',
            'from_name' => $required . '|nullable|string|max:255',

            'imap_host' => $required . '|string|max:255',
            'imap_port' => $required . '|integer|min:1|max:65535',
            'imap_encryption' => $required . '|in:ssl,tls,none',
            'imap_validate_cert' => $required . '|boolean',
            'imap_username' => $required . '|nullable|string|max:255',
            'imap_password' => $passwordRequired . '|string|max:1000',

            'smtp_host' => $required . '|string|max:255',
            'smtp_port' => $required . '|integer|min:1|max:65535',
            'smtp_encryption' => $required . '|in:ssl,tls,none',
            'smtp_username' => $required . '|nullable|string|max:255',
            'smtp_password' => $passwordRequired . '|string|max:1000',

            'is_active' => $required . '|boolean',
            'notes' => $required . '|nullable|string|max:2000',
        ];
    }
}
