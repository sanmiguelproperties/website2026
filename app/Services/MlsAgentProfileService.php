<?php

namespace App\Services;

use App\Models\MLSAgent;
use App\Models\MLSOffice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MlsAgentProfileService
{
    public function createForUser(User $user): MLSAgent
    {
        $this->ensureUserIsAgent($user);

        return DB::transaction(function () use ($user): MLSAgent {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if (MLSAgent::query()->where('user_id', $lockedUser->id)->exists()) {
                throw ValidationException::withMessages([
                    'user_id' => ['El usuario ya tiene un perfil de agente MLS relacionado.'],
                ]);
            }

            $office = $this->primaryOffice();

            return MLSAgent::create([
                'mls_agent_id' => null,
                'is_manual' => true,
                'name' => $lockedUser->name,
                'email' => $lockedUser->agent_public_email ?: $lockedUser->email,
                'phone' => $lockedUser->agent_phone,
                'bio_es' => $lockedUser->agent_bio,
                'photo_media_asset_id' => $lockedUser->agent_profile_media_asset_id ?: $lockedUser->profile_image_id,
                'mls_office_id' => $office->mls_office_id,
                'office_name' => $office->name,
                'is_active' => $lockedUser->is_active,
                'user_id' => $lockedUser->id,
            ]);
        });
    }

    public function linkUser(MLSAgent $agent, ?User $user, bool $replaceExisting = false): MLSAgent
    {
        if ($user) {
            $this->ensureUserIsAgent($user);
        }

        return DB::transaction(function () use ($agent, $user, $replaceExisting): MLSAgent {
            $lockedAgent = MLSAgent::query()->lockForUpdate()->findOrFail($agent->id);

            if ($user) {
                $this->ensureAgentBelongsToPrimaryOffice($lockedAgent);

                $alreadyLinked = MLSAgent::query()
                    ->where('user_id', $user->id)
                    ->whereKeyNot($lockedAgent->id)
                    ->lockForUpdate()
                    ->first();

                if ($alreadyLinked) {
                    if (! $replaceExisting) {
                        throw ValidationException::withMessages([
                            'user_id' => ['El usuario ya tiene otro perfil de agente MLS relacionado.'],
                        ]);
                    }

                    $alreadyLinked->update(['user_id' => null]);
                }
            }

            $lockedAgent->update(['user_id' => $user?->id]);

            return $lockedAgent->fresh(['user', 'office', 'photoMediaAsset']);
        });
    }

    public function assignPrimaryOffice(array $data): array
    {
        $office = $this->primaryOffice();
        $data['mls_office_id'] = $office->mls_office_id;
        $data['office_name'] = $office->name;

        return $data;
    }

    public function primaryOffice(): MLSOffice
    {
        $office = MLSOffice::query()->primary()->orderBy('mls_office_id')->first();

        if (! $office) {
            throw ValidationException::withMessages([
                'mls_office_id' => ['Configura una agencia MLS principal antes de crear perfiles manuales.'],
            ]);
        }

        return $office;
    }

    public function ensureAgentBelongsToPrimaryOffice(MLSAgent $agent): void
    {
        $office = $this->primaryOffice();

        if ((int) $agent->mls_office_id !== (int) $office->mls_office_id) {
            throw ValidationException::withMessages([
                'mls_agent_profile_id' => ['Solo puedes relacionar perfiles de agentes pertenecientes a la agencia MLS principal.'],
            ]);
        }
    }

    public function ensureUserIsAgent(User $user): void
    {
        $hasAgentRole = $user->roles()
            ->whereIn('name', ['agente', 'agent'])
            ->exists();

        if (! $hasAgentRole) {
            throw ValidationException::withMessages([
                'user_id' => ['El usuario debe tener el rol agente antes de relacionar un perfil MLS.'],
            ]);
        }
    }
}
