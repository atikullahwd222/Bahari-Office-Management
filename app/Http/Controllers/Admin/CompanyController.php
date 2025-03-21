<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Redirect;


class CompanyController extends Controller
{
    public function companySettings(Request $request)
    {
        $user = auth()->user();
        $company = CompanySetting::where('company_uid', $user->company_uid)->first();
        return view('admin.company-settings', compact('company'));
    }

    public function companyLogoUpdate(Request $request)
    {
        $user = auth()->user();
        $company = CompanySetting::where('company_uid', $user->company_uid)->first();
        if ($request->company_logo) {
            if ($request->hasFile('company_logo')) {

                if ($company->company_logo !== 'assets/img/company_logo/logo.png' && file_exists(public_path($company->company_logo))) {
                    unlink(public_path($company->company_logo));
                }

                $fileName = time() . '_' . $request->file('company_logo')->getClientOriginalName();
                $destinationPath = public_path('assets/img/company_logo');
                $request->file('company_logo')->move($destinationPath, $fileName);
                $profile_pic_path = 'assets/img/company_logo/' . $fileName;
                $company->company_logo = $profile_pic_path;
                $company->save();
            }
        } else {
            return Redirect::route('admin.company.branding', 'tab=company-image')->with(['verify' => 'company-info-updated', 'status' => 'danger', 'message' => 'Select a photo']);
        }
        return Redirect::route('admin.company.branding', 'tab=company-image')->with(['verify' => 'company-info-updated', 'status' => 'success', 'message' => 'Company logo updated successfully']);
    }

    public function companyFaviconUpdate(Request $request)
    {
        $user = auth()->user();
        $company = CompanySetting::where('company_uid', $user->company_uid)->first();

        if ($request->hasFile('company_favicon')) {
            $oldFavicon = $company->company_favicon;

            // Prevent deletion of default favicons (both .ico and .png)
            $defaultFavicons = [
                'assets/img/company_favicon/favicon.ico',
                'assets/img/company_favicon/favicon.png'
            ];

            if (!empty($oldFavicon) && !in_array($oldFavicon, $defaultFavicons) && file_exists(public_path($oldFavicon))) {
                unlink(public_path($oldFavicon));
            }

            // Upload new favicon
            $fileName = time() . '_' . $request->file('company_favicon')->getClientOriginalName();
            $destinationPath = public_path('assets/img/company_favicon');
            $request->file('company_favicon')->move($destinationPath, $fileName);
            $company->company_favicon = 'assets/img/company_favicon/' . $fileName;
            $company->save();

            return Redirect::route('admin.company.branding', 'tab=company-image')
                ->with(['verify' => 'company-favicon-updated', 'status' => 'success', 'message' => 'Company favicon updated successfully']);
        }

        return Redirect::route('admin.company.branding', 'tab=company-image')
            ->with(['verify' => 'company-favicon-updated', 'status' => 'danger', 'message' => 'Select a photo']);
    }





    public function companyInfo()
    {
        $user = auth()->user();
        $company = CompanySetting::where('company_uid', $user->company_uid)->first();
        return view('admin.company-info', compact('company'));
    }

    public function companyInfoUpdate(Request $request, $uid)
    {
        $user = auth()->user();
        $company = CompanySetting::where('id', $uid)->first();

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|string|email|max:255|unique:company_settings,company_email,' . $company->id,
            'company_phone' => 'required|string|max:15',
            'company_address' => 'required|string|max:255',
            'company_city' => 'required|string|max:255',
            'company_state' => 'required|string|max:255',
            'company_website' => 'required|string|max:255',
            'company_facebook' => 'required|string|max:255',
        ]);

        $company->company_name = $validated['company_name'];
        $company->company_email = $validated['company_email'];
        $company->company_phone = $validated['company_phone'];
        $company->company_address = $validated['company_address'];
        $company->company_city = $validated['company_city'];
        $company->company_state = $validated['company_state'];
        $company->company_website = $validated['company_website'];
        $company->company_facebook = $validated['company_facebook'];
        $company->save();


        return Redirect::route('admin.company.company-info')->with(['verify' => 'company-info-updated', 'status' => 'success', 'message' => 'Company info updated successfully']);
    }
}
