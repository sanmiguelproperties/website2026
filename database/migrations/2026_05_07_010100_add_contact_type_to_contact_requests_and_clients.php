<?php

use App\Models\ContactRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('contact_requests', 'contact_type')) {
                $table->string('contact_type', 50)->nullable()->after('lead_type')->index();
            }
        });

        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'contact_type')) {
                $table->string('contact_type', 50)->nullable()->after('source')->index();
            }
        });

        DB::table('contact_requests')
            ->whereNull('contact_type')
            ->where(function ($query): void {
                $query->where('lead_type', ContactRequest::LEAD_TYPE_SELLER)
                    ->orWhere('source', ContactRequest::SOURCE_SELLER_FORM);
            })
            ->update(['contact_type' => ContactRequest::CONTACT_TYPE_SELLER]);

        DB::table('contact_requests')
            ->whereNull('contact_type')
            ->whereIn('lead_type', [
                ContactRequest::LEAD_TYPE_BUYER,
                ContactRequest::LEAD_TYPE_RENTER,
                ContactRequest::LEAD_TYPE_INVESTOR,
            ])
            ->update(['contact_type' => ContactRequest::CONTACT_TYPE_BUYER]);

        DB::table('clients')
            ->whereNull('contact_type')
            ->whereNotNull('contact_request_id')
            ->select(['id', 'contact_request_id'])
            ->orderBy('id')
            ->chunkById(100, function ($clients): void {
                $contactTypes = DB::table('contact_requests')
                    ->whereIn('id', $clients->pluck('contact_request_id')->filter()->all())
                    ->whereNotNull('contact_type')
                    ->pluck('contact_type', 'id');

                foreach ($clients as $client) {
                    $contactType = $contactTypes->get($client->contact_request_id);

                    if ($contactType) {
                        DB::table('clients')
                            ->where('id', $client->id)
                            ->update(['contact_type' => $contactType]);
                    }
                }
            });

        DB::table('clients')
            ->whereNull('contact_type')
            ->where('source', ContactRequest::SOURCE_SELLER_FORM)
            ->update(['contact_type' => ContactRequest::CONTACT_TYPE_SELLER]);

        DB::table('clients')
            ->whereNull('contact_type')
            ->where('source', ContactRequest::SOURCE_PROPERTY_FORM)
            ->update(['contact_type' => ContactRequest::CONTACT_TYPE_BUYER]);
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'contact_type')) {
                $table->dropColumn('contact_type');
            }
        });

        Schema::table('contact_requests', function (Blueprint $table) {
            if (Schema::hasColumn('contact_requests', 'contact_type')) {
                $table->dropColumn('contact_type');
            }
        });
    }
};
