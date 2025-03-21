<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Hash;

class UsersImport implements ToModel, WithHeadingRow, WithValidation
{
    private $company_uid;

    public function __construct($company_uid)
    {
        $this->company_uid = $company_uid;
    }

    public function model(array $row)
    {
        return new User([
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'address' => $row['address'] ?? '',
            'city' => $row['city'] ?? '',
            'state' => $row['state'] ?? '',
            'password' => Hash::make('password123'), // Default password
            'company_uid' => $this->company_uid,
            'role' => $row['role'] ?? 'user', // Get role from Excel or default to 'user'
            'status' => 'active',
            'profile_photo' => 'assets/img/avatars/default.png',
            'email_verified_at' => now(),
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:15',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'role' => 'nullable|string|in:user,admin', // Add validation for role
        ];
    }
}
