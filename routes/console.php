<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('emails:sync {--account-id=} {--limit=50} {--folder=INBOX}', function () {
    $accountId = $this->option('account-id');
    $limit = max(1, min(200, (int) $this->option('limit')));
    $folder = (string) $this->option('folder');

    $query = \App\Models\CorporateEmailAccount::query()->where('is_active', true);
    if (!empty($accountId)) {
        $query->where('id', (int) $accountId);
    }

    $accounts = $query->orderBy('id')->get();

    if ($accounts->isEmpty()) {
        $this->warn('No hay cuentas activas para sincronizar.');
        return;
    }

    $service = app(\App\Services\CorporateEmailService::class);

    $this->info('Sincronizando correos corporativos...');
    foreach ($accounts as $account) {
        $this->line("Cuenta #{$account->id}: {$account->name} <{$account->email_address}>");
        $result = $service->syncInbox($account, $limit, $folder);

        if (!($result['success'] ?? false)) {
            $this->error('  Error: ' . ($result['message'] ?? 'desconocido'));
            continue;
        }

        $stats = $result['stats'] ?? [];
        $this->info('  Importados: ' . ($stats['imported'] ?? 0) . ' | Omitidos: ' . ($stats['skipped'] ?? 0) . ' | Errores: ' . ($stats['errors'] ?? 0));
    }
})->purpose('Sincroniza inbox de cuentas corporativas por IMAP');
