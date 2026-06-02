<?php

namespace App\Actions\Fortify;

use App\Jobs\SendUserToBitrix24;
use App\Models\CrmRole;
use App\Models\Team;
use App\Models\TeamMemberProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'country_code' => ['required', 'string', 'max:5', 'regex:/^\+[0-9]{1,4}$/'],
            'phone' => ['required', 'string', 'min:6', 'max:25', 'regex:/^[0-9\s\-]+$/'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ], [
            'country_code.regex' => __('Selecciona un código de país válido.'),
            'phone.regex' => __('El teléfono solo puede contener números, espacios y guiones.'),
        ])->validate();

        $user = DB::transaction(function () use ($input) {
            return tap(User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'country_code' => $input['country_code'],
                'phone' => preg_replace('/\s+/', '', $input['phone']),
                'password' => Hash::make($input['password']),
            ]), function (User $user) {
                $this->createTeam($user);
            });
        });

        // Sincronizar con Bitrix24 (async via cola). El job tiene retries y
        // captura sus propios errores; si Bitrix está caído NO falla el registro.
        try {
            SendUserToBitrix24::dispatch($user);
        } catch (\Throwable $e) {
            // Fail-safe: si el dispatch mismo falla (ej. conexión a la cola),
            // registramos pero seguimos. El registro del usuario YA fue exitoso.
            \Illuminate\Support\Facades\Log::error('No se pudo despachar SendUserToBitrix24', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        return $user;
    }

    /**
     * Create a personal team for the user.
     */
    protected function createTeam(User $user): void
    {
        $team = Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]);

        $user->ownedTeams()->save($team);

        // Sembrar roles del sistema (Administrador + Editor) para el team nuevo
        CrmRole::seedDefaultsForTeam($team);

        // Asignar al owner el rol Administrador en su perfil
        $adminRole = CrmRole::where('team_id', $team->id)
            ->where('is_default', true)
            ->first();

        if ($adminRole) {
            TeamMemberProfile::firstOrCreate(
                ['team_id' => $team->id, 'user_id' => $user->id],
                ['correo' => $user->email, 'crm_role_id' => $adminRole->id]
            );
        }
    }
}
