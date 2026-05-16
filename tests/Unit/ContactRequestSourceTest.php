<?php

namespace Tests\Unit;

use App\Models\ContactRequest;
use PHPUnit\Framework\TestCase;

class ContactRequestSourceTest extends TestCase
{
    public function test_manual_lead_sources_are_available_for_crm_leads(): void
    {
        $this->assertArrayHasKey(ContactRequest::SOURCE_REFERRAL, ContactRequest::manualSourceLabels());
        $this->assertArrayHasKey(ContactRequest::SOURCE_FACEBOOK, ContactRequest::manualSourceLabels());
        $this->assertArrayHasKey(ContactRequest::SOURCE_TIKTOK, ContactRequest::manualSourceLabels());
        $this->assertArrayHasKey(ContactRequest::SOURCE_INSTAGRAM, ContactRequest::manualSourceLabels());

        $this->assertContains(ContactRequest::SOURCE_REFERRAL, ContactRequest::publicSources());
        $this->assertContains(ContactRequest::SOURCE_FACEBOOK, ContactRequest::publicSources());
    }
}
