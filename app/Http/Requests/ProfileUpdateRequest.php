<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id), // Ignore the current user's email
            ],
            'phone' => ['nullable', 'string', 'max:20'], // Phone number is optional but needs to be a valid string
            'address' => ['nullable', 'string', 'max:255'], // Address is optional but if provided, it should be a string
            'city' => ['nullable', 'string', 'max:255'], // City is optional
            'state' => ['nullable', 'string', 'max:255'], // State is optional
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:800'], // Profile photo is optional but must be a valid image if provided
        ];
    }
}
