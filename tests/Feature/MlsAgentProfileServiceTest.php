<?php

namespace Tests\Feature;

use App\Http\Controllers\MLSAgentController;
use App\Http\Controllers\UserController;
use App\Models\Agency;
use App\Models\MLSAgent;
use App\Models\MLSOffice;
use App\Models\Property;
use App\Models\User;
use App\Services\MlsAgentProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MlsAgentProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_manual_public_profile_for_an_agent_user_in_the_primary_office(): void
    {
        $office = $this->createPrimaryOffice();
        $user = $this->createAgentUser();

        $profile = app(MlsAgentProfileService::class)->createForUser($user);

        $this->assertTrue($profile->is_manual);
        $this->assertNull($profile->mls_agent_id);
        $this->assertSame($user->id, $profile->user_id);
        $this->assertSame($office->mls_office_id, $profile->mls_office_id);
        $this->assertSame($office->name, $profile->office_name);
        $this->assertSame('local-'.$profile->id, $profile->public_id);

        $this->getJson('/api/public/mls-agents/'.$profile->public_id)
            ->assertOk()
            ->assertJsonPath('data.agent.id', $profile->id)
            ->assertJsonPath('data.agent.public_id', $profile->public_id);

        Agency::create([
            'id' => 1,
            'name' => 'Local Agency',
        ]);
        $property = Property::create([
            'agency_id' => 1,
            'source' => Property::SOURCE_MANUAL,
            'published' => true,
            'title' => 'Property linked to local profile',
        ]);
        $profile->properties()->attach($property->id, ['is_primary' => true]);

        $this->getJson('/api/public/properties?mls_agent_id='.$profile->public_id)
            ->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.id', $property->id);
    }

    public function test_it_replaces_the_profile_link_when_requested_from_the_user_editor(): void
    {
        $office = $this->createPrimaryOffice();
        $user = $this->createAgentUser();
        $first = MLSAgent::create([
            'mls_agent_id' => 101,
            'name' => 'Existing MLS profile',
            'mls_office_id' => $office->mls_office_id,
            'user_id' => $user->id,
        ]);
        $replacement = MLSAgent::create([
            'mls_agent_id' => 102,
            'name' => 'Replacement MLS profile',
            'mls_office_id' => $office->mls_office_id,
        ]);

        app(MlsAgentProfileService::class)->linkUser($replacement, $user, true);

        $this->assertNull($first->fresh()->user_id);
        $this->assertSame($user->id, $replacement->fresh()->user_id);
    }

    public function test_it_rejects_linking_a_profile_to_a_user_without_the_agent_role(): void
    {
        $user = User::factory()->create();
        $profile = MLSAgent::create([
            'mls_agent_id' => 103,
            'name' => 'MLS profile',
        ]);

        $this->expectException(ValidationException::class);

        app(MlsAgentProfileService::class)->linkUser($profile, $user);
    }

    public function test_it_rejects_linking_an_external_office_profile_to_an_agent_user(): void
    {
        $this->createPrimaryOffice();
        $externalOffice = $this->createExternalOffice();
        $user = $this->createAgentUser();
        $profile = MLSAgent::create([
            'mls_agent_id' => 104,
            'name' => 'External MLS profile',
            'mls_office_id' => $externalOffice->mls_office_id,
        ]);

        $this->expectException(ValidationException::class);

        app(MlsAgentProfileService::class)->linkUser($profile, $user);
    }

    public function test_user_profile_options_only_include_profiles_from_the_primary_office(): void
    {
        $primaryOffice = $this->createPrimaryOffice();
        $externalOffice = $this->createExternalOffice();
        $primaryProfile = MLSAgent::create([
            'mls_agent_id' => 105,
            'name' => 'Primary office profile',
            'mls_office_id' => $primaryOffice->mls_office_id,
        ]);
        MLSAgent::create([
            'mls_agent_id' => 106,
            'name' => 'External office profile',
            'mls_office_id' => $externalOffice->mls_office_id,
        ]);

        $response = app(UserController::class)->mlsAgentOptions(
            Request::create('/api/users/mls-agent-options')
        );

        $this->assertSame(
            [$primaryProfile->id],
            collect($response->getData(true)['data'])->pluck('id')->all()
        );
    }

    public function test_store_forces_a_new_agent_into_the_primary_office(): void
    {
        $primaryOffice = $this->createPrimaryOffice();
        $externalOffice = $this->createExternalOffice();

        $response = app(MLSAgentController::class)->store(Request::create('/api/mls-agents', 'POST', [
            'name' => 'New local profile',
            'mls_office_id' => $externalOffice->mls_office_id,
            'office_name' => $externalOffice->name,
        ]));

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame($primaryOffice->mls_office_id, $response->getData(true)['data']['mls_office_id']);
        $this->assertSame($primaryOffice->name, $response->getData(true)['data']['office_name']);
    }

    public function test_update_rejects_moving_a_linked_profile_to_an_external_office(): void
    {
        $primaryOffice = $this->createPrimaryOffice();
        $externalOffice = $this->createExternalOffice();
        $user = $this->createAgentUser();
        $profile = MLSAgent::create([
            'mls_agent_id' => 107,
            'name' => 'Linked primary profile',
            'mls_office_id' => $primaryOffice->mls_office_id,
            'user_id' => $user->id,
        ]);

        $response = app(MLSAgentController::class)->update(
            Request::create('/api/mls-agents/'.$profile->id, 'PATCH', [
                'mls_office_id' => $externalOffice->mls_office_id,
            ]),
            $profile
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame($primaryOffice->mls_office_id, $profile->fresh()->mls_office_id);
    }

    private function createPrimaryOffice(): MLSOffice
    {
        return MLSOffice::create([
            'mls_office_id' => 15,
            'name' => 'Primary MLS Office',
            'is_primary' => true,
        ]);
    }

    private function createExternalOffice(): MLSOffice
    {
        return MLSOffice::create([
            'mls_office_id' => 16,
            'name' => 'External MLS Office',
            'is_primary' => false,
        ]);
    }

    private function createAgentUser(): User
    {
        $user = User::factory()->create([
            'name' => 'Local Agent',
            'email' => 'local-agent@example.com',
        ]);
        $role = Role::findOrCreate('agente', 'web');
        $user->assignRole($role);

        return $user;
    }
}
