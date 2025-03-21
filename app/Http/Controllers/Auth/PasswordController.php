<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Redirect;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request)
    {

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return Redirect::back()->with([
                'verify' => 'profile-updated',
                'status' => 'danger',
                'message' => 'Current Password is not correct'
            ]);
        }

        if($request->password !== $request->password_confirmation){
            return Redirect::back()->with([
                'verify' => 'profile-updated',
                'status' => 'danger',
                'message' => 'Password and Confirm Password do not match'
            ]);
        }

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return Redirect::back()->with([
            'verify' => 'profile-updated',
            'status' => 'success',
            'message' => 'Password updated successfully'
        ]);
    }
}
