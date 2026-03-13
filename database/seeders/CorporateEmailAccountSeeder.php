<?php

namespace Database\Seeders;

use App\Models\CorporateEmailAccount;
use Illuminate\Database\Seeder;

class CorporateEmailAccountSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        CorporateEmailAccount::updateOrCreate(
            ['email_address' => 'hola@systemsgg.com'],
            [
                'user_id' => null,
                'name' => 'Cuenta Corporativa SystemsGG',
                'from_name' => 'SystemsGG',

                // IMAP (secure SSL/TLS recommended)
                'imap_host' => 'systemsgg.com',
                'imap_port' => 993,
                'imap_encryption' => 'ssl',
                'imap_validate_cert' => true,
                'imap_username' => 'hola@systemsgg.com',
                'imap_password' => '2535570Panda',

                // SMTP (secure SSL/TLS recommended)
                'smtp_host' => 'systemsgg.com',
                'smtp_port' => 465,
                'smtp_encryption' => 'ssl',
                'smtp_username' => 'hola@systemsgg.com',
                'smtp_password' => '2535570Panda',

                'is_active' => true,
                'notes' => "CalDAV URL: https://systemsgg.com:2080/calendars/hola@systemsgg.com/calendar\n"
                    . "CardDAV URL: https://systemsgg.com:2080/addressbooks/hola@systemsgg.com/addressbook\n"
                    . "Server secure: https://systemsgg.com:2080\n"
                    . "Server non-SSL: http://mail.systemsgg.com:2079",
            ]
        );
    }
}
