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
            if (!Schema::hasColumn('contact_requests', 'lead_type')) {
                $table->string('lead_type', 50)->nullable()->after('source')->index();
            }

            if (!Schema::hasColumn('contact_requests', 'property_context')) {
                $table->string('property_context', 50)->nullable()->after('lead_type')->index();
            }

            if (!Schema::hasColumn('contact_requests', 'interest')) {
                $table->string('interest', 100)->nullable()->after('property_context')->index();
            }

            if (!Schema::hasColumn('contact_requests', 'property_address')) {
                $table->string('property_address')->nullable()->after('property_public_id');
            }

            if (!Schema::hasColumn('contact_requests', 'source_url')) {
                $table->text('source_url')->nullable()->after('source');
            }

            if (!Schema::hasColumn('contact_requests', 'referrer_url')) {
                $table->text('referrer_url')->nullable()->after('source_url');
            }

            foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $utmColumn) {
                if (!Schema::hasColumn('contact_requests', $utmColumn)) {
                    $table->string($utmColumn)->nullable()->after('referrer_url')->index();
                }
            }

            if (!Schema::hasColumn('contact_requests', 'locale')) {
                $table->string('locale', 10)->nullable()->after('phone');
            }

            if (!Schema::hasColumn('contact_requests', 'privacy_accepted_at')) {
                $table->dateTime('privacy_accepted_at')->nullable()->after('happened_at');
            }
        });

        DB::table('contact_requests')
            ->where('source', ContactRequest::SOURCE_PROPERTY_FORM)
            ->whereNull('lead_type')
            ->update([
                'lead_type' => ContactRequest::LEAD_TYPE_BUYER,
                'property_context' => ContactRequest::PROPERTY_CONTEXT_EXISTING_LISTING,
            ]);

        DB::table('contact_requests')
            ->where('source', ContactRequest::SOURCE_SELLER_FORM)
            ->whereNull('lead_type')
            ->update([
                'lead_type' => ContactRequest::LEAD_TYPE_SELLER,
                'property_context' => ContactRequest::PROPERTY_CONTEXT_SELLER_PROPERTY,
            ]);
    }

    public function down(): void
    {
        Schema::table('contact_requests', function (Blueprint $table) {
            foreach ([
                'lead_type',
                'property_context',
                'interest',
                'property_address',
                'source_url',
                'referrer_url',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_term',
                'utm_content',
                'locale',
                'privacy_accepted_at',
            ] as $column) {
                if (Schema::hasColumn('contact_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
