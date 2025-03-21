<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
class ProfileController extends Controller

{
    /**
     * Display the user's profile form.
     */

    //  public function rules()
    // {
    //     return [
    //         'first_name' => 'required|string|max:255',
    //         'last_name' => 'required|string|max:255',
    //         'email' => 'required|email|max:255|unique:users,email,' . $this->user()->id,
    //         'phone' => 'nullable|string|max:20',
    //         'address' => 'nullable|string|max:255',
    //         'city' => 'nullable|string|max:255',
    //         'state' => 'nullable|string|max:255',
    //         'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:800',
    //     ];
    // }


    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }


    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        return Redirect::back()->with(['verify'=>'profile-updated', 'status'=>'success', 'message'=>'Profile updated successfully']);
    }

    public function update_photo(Request $request)
    {
        $user = $request->user();


        if($request->profile_photo){
            if ($request->hasFile('profile_photo')) {

                if ($user->profile_photo !== 'assets/img/avatars/default.png' && file_exists(public_path($user->profile_photo))) {
                    unlink(public_path($user->profile_photo));
                }

                $fileName = time() . '_' . $request->file('profile_photo')->getClientOriginalName();
                $destinationPath = public_path('assets/img/avatars');
                $request->file('profile_photo')->move($destinationPath, $fileName);
                $profile_pic_path = 'assets/img/avatars/' . $fileName;
                $user->profile_photo = $profile_pic_path;
                $user->save();
            }
        } else {
            return Redirect::back()->with(['verify'=>'profile-updated', 'status'=>'danger', 'message'=>'Select a photo']);
        }

        return Redirect::back()->with(['verify'=>'profile-updated', 'status'=>'success', 'message'=>'Profile updated successfully']);
    }

    public function settings(): View
    {
        return view('profile.settings');
    }



    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
