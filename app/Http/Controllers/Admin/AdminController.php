<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Redirect;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    public function companySettings(Request $request)
    {
        $company_uid = auth()->user()->company_uid;

        if (auth()->user()->role == 'super-admin') {
            // Get the selected company name from the request
            $company_name = $request->input('company_name', null); // Use company_name to filter

            if ($company_name) {
                // If a company name is selected, filter employees by company_uid
                $company = CompanySetting::where('company_name', $company_name)->first(); // Get the company data for display

                // Fetch employees based on the selected company's UID
                $employees = User::with('company')
                    ->where('company_uid', $company->company_uid)
                    ->get();
            } else {
                // If no company is selected, show all employees and companies
                $employees = User::with('company')->get();
                $company = null;  // No company selected
            }

            // Fetch all companies for the dropdown
            $companies = CompanySetting::all();
        } else {
            // For non-super-admin, just show the employees of the logged-in user's company
            $company = CompanySetting::where('company_uid', $company_uid)->first();
            $employees = User::where('company_uid', $company_uid)->get();
            $companies = [];  // No need for this variable for non-super-admin users
        }

        // Pass employees, company, and all companies (for super-admin) to the view
        return view('admin.company-settings', compact('employees', 'company', 'companies'));
    }

    public function companyUsers(Request $request)
    {
        $company_uid = auth()->user()->company_uid;

        if (auth()->user()->role == 'super-admin') {
            // Get the selected company name from the request
            $company_name = $request->input('company_name', null); // Use company_name to filter

            if ($company_name) {
                // If a company name is selected, filter employees by company_uid
                $company = CompanySetting::where('company_name', $company_name)->first(); // Get the company data for display

                // Fetch employees based on the selected company's UID
                $employees = User::with('company')
                    ->where('company_uid', $company->company_uid)
                    ->get();
            } else {
                // If no company is selected, show all employees and companies
                $employees = User::with('company')->get();
                $company = null;  // No company selected
            }

            // Fetch all companies for the dropdown
            $companies = CompanySetting::all();
        } else {
            // For non-super-admin, just show the employees of the logged-in user's company
            $company = CompanySetting::where('company_uid', $company_uid)->first();
            $employees = User::where('company_uid', $company_uid)->get();
            $companies = [];  // No need for this variable for non-super-admin users
        }

        // Pass employees, company, and all companies (for super-admin) to the view
        return view('admin.users', compact('employees', 'company', 'companies'));
    }


    public function createUser()
    {
        $companies = CompanySetting::all();
        return view('admin.createUser', compact('companies'));
    }

    public function storeUser(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:15',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Prepare additional data
        $validated['password'] = bcrypt($request->password);
        $validated['company_uid'] = auth()->user()->company_uid;
        $validated['role'] = 'user';
        $validated['status'] = 'active';
        $validated['profile_photo'] = 'assets/img/avatars/default.png';
        $validated['email_verified_at'] = now();

        $user = User::create($validated);

        if ($request->hasFile('profile_photo')) {

            $fileName = time() . '_' . $request->file('profile_photo')->getClientOriginalName();
            $destinationPath = public_path('assets/img/avatars');
            $request->file('profile_photo')->move($destinationPath, $fileName);
            $profile_pic_path = 'assets/img/avatars/' . $fileName;
            $user->profile_photo = $profile_pic_path;
            $user->save();
        }

        return redirect()->route('admin.company.user.edit', $user->id)
                        ->with([
                            'verify' => 'profile-updated',
                            'status' => 'success',
                            'message' => 'User created successfully'
                        ]);
    }


    public function editUser($id)
    {
        $employee = User::findOrFail($id);
        return view('admin.editUser', compact('employee'));
    }

    public function profileUpdate(Request $request, $id)
    {
        $employee = User::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $employee->id,
            'phone' => 'required|string|max:15',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
        ]);

        $employee->first_name = $validated['first_name'];
        $employee->last_name = $validated['last_name'];
        $employee->email = $validated['email'];
        $employee->phone = $validated['phone'];
        $employee->address = $validated['address'];
        $employee->city = $validated['city'];
        $employee->state = $validated['state'];
        $employee->save();

        return Redirect::route('admin.company.user.edit', $id)->with([
            'verify' => 'profile-updated',
            'status' => 'success',
            'message' => 'Profile updated successfully'
        ]);
    }

    public function updatePhoto(Request $request, $id)
    {
        $user = User::findOrFail($id);


        if ($request->profile_photo) {
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
            return Redirect::route('admin.company.user.edit', $id)->with(['verify' => 'profile-updated', 'status' => 'danger', 'message' => 'Select a photo']);
        }

        return Redirect::route('admin.company.user.edit', $id)->with(['verify' => 'profile-updated', 'status' => 'success', 'message' => 'Profile updated successfully']);
    }

    public function destroyUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Delete profile photo if it's not the default
            if ($user->profile_photo !== 'assets/img/avatars/default.png' && file_exists(public_path($user->profile_photo))) {
                unlink(public_path($user->profile_photo));
            }

            $user->delete();

            return redirect()->route('admin.company.users')
                ->with('verify', 'profile-updated')
                ->with('status', 'success')
                ->with('message', 'User deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.company.users')
                ->with('verify', 'profile-updated')
                ->with('status', 'danger')
                ->with('message', 'Error deleting user. Please try again.');
        }
    }

    public function importUsers(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            $company_uid = auth()->user()->role === 'super-admin'
                ? $request->company_uid
                : auth()->user()->company_uid;

            Excel::import(new UsersImport($company_uid), $request->file('excel_file'));

            return redirect()->route('admin.company.users')
                ->with('verify', 'profile-updated')
                ->with('status', 'success')
                ->with('message', 'Users imported successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('verify', 'profile-updated')
                ->with('status', 'danger')
                ->with('message', 'Error importing users: ' . $e->getMessage());
        }
    }
}
