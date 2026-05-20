<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocialCredentialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'platform' => 'required|string|in:facebook,tiktok,instagram,linkedin,twitter',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'platform.required' => 'The platform field is required.',
            'platform.in' => 'The platform must be one of: facebook, tiktok, instagram, linkedin, twitter.',
            'username.required' => 'The username field is required.',
            'password.required' => 'The password field is required.',
        ];
    }
}
