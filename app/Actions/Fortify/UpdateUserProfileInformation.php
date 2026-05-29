<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo'        => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'country_code' => ['nullable', 'string', 'max:5', 'regex:/^\+[0-9]{1,4}$/'],
            'phone'        => ['nullable', 'string', 'min:6', 'max:25', 'regex:/^[0-9\s\-]+$/'],
        ], [
            'country_code.regex' => __('Selecciona un código de país válido.'),
            'phone.regex'        => __('El teléfono solo puede contener números, espacios y guiones.'),
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        $phoneClean = isset($input['phone']) ? preg_replace('/\s+/', '', (string) $input['phone']) : null;

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input, $phoneClean);
        } else {
            $user->forceFill([
                'name'         => $input['name'],
                'email'        => $input['email'],
                'country_code' => $input['country_code'] ?? $user->country_code,
                'phone'        => $phoneClean,
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input, ?string $phoneClean = null): void
    {
        $user->forceFill([
            'name'              => $input['name'],
            'email'             => $input['email'],
            'country_code'      => $input['country_code'] ?? $user->country_code,
            'phone'             => $phoneClean,
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
