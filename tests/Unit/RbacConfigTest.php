<?php

namespace Tests\Unit;

use Tests\TestCase;

class RbacConfigTest extends TestCase
{
    public function test_agent_property_permissions_are_read_only(): void
    {
        $permissions = config('rbac.roles.agent.permissions', []);

        $this->assertContains('properties.view', $permissions);
        $this->assertContains('properties.view.all', $permissions);

        $this->assertNotContains('properties.create', $permissions);
        $this->assertNotContains('properties.edit', $permissions);
        $this->assertNotContains('properties.edit.own', $permissions);
        $this->assertNotContains('properties.delete', $permissions);
        $this->assertNotContains('properties.delete.own', $permissions);
        $this->assertNotContains('properties.restore', $permissions);
    }
}
