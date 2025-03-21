<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OnetimeExpens;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\OnetimeExpensesImport;
use App\Imports\MonthlyExpensesImport;

class OnetimeExpensesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = OnetimeExpens::with('company');
        $companies = [];

        if ($user->role === 'super-admin') {
            // Superadmin can see all companies' data
            $companies = CompanySetting::all();
        } else {
            // Regular users can only see their own company's data
            $query->where('company_uid', $user->company_uid);
        }

        $expenses = $query->latest('date')->paginate(10);

        return view('admin.payroll.onetime_expenses.index', compact('expenses', 'companies'));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->role === 'super-admin') {
            $companies = CompanySetting::all();
        } else {
            $companies = CompanySetting::where('company_uid', $user->company_uid)->get();
        }

        return view('admin.payroll.onetime_expenses.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_uid' => ['required', 'string', 'exists:company_settings,company_uid'],
            'purpose' => ['required', 'string', 'max:255'],
            'pay_to' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'payment_status' => ['nullable', 'string', 'in:pending,paid,cancelled'],
        ]);

        try {
            OnetimeExpens::create([
                'company_uid' => $request->company_uid,
                'purpose' => $request->purpose,
                'pay_to' => $request->pay_to,
                'amount' => $request->amount,
                'date' => $request->date,
                'description' => $request->description,
                'payment_status' => $request->payment_status ?? 'pending',
            ]);

            return redirect()->route('admin.payroll.onetime-expenses')
                ->with('verify', 'expense-updated')
                ->with('status', 'success')
                ->with('message', 'One-time expense created successfully.');
        } catch (\Exception $e) {
            \Log::error('One-time Expense Creation Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('verify', 'expense-updated')
                ->with('status', 'danger')
                ->with('message', 'Error creating one-time expense. Please try again.');
        }
    }

    public function edit($id)
    {
        try {
            $expense = OnetimeExpens::findOrFail($id);

            if (Auth::user()->role !== 'super-admin' && Auth::user()->company_uid !== $expense->company_uid) {
                return redirect()->route('admin.payroll.onetime-expenses')
                    ->with('verify', 'expense-updated')
                    ->with('status', 'danger')
                    ->with('message', 'You do not have permission to edit this expense.');
            }

            $companies = [];
            if (Auth::user()->role === 'super-admin') {
                $companies = CompanySetting::all();
            } else {
                $companies = CompanySetting::where('company_uid', Auth::user()->company_uid)->get();
            }

            return view('admin.payroll.onetime_expenses.edit', compact('expense', 'companies'));
        } catch (\Exception $e) {
            \Log::error('One-time Expense Edit Error: ' . $e->getMessage());
            return redirect()->route('admin.payroll.onetime-expenses')
                ->with('verify', 'expense-updated')
                ->with('status', 'danger')
                ->with('message', 'Error accessing expense. Please try again.');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'company_uid' => ['required', 'string', 'exists:company_settings,company_uid'],
            'purpose' => ['required', 'string', 'max:255'],
            'pay_to' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'payment_status' => ['required', 'string', 'in:pending,paid,cancelled'],
        ]);

        try {
            $expense = OnetimeExpens::findOrFail($id);

            if (Auth::user()->role !== 'super-admin' && Auth::user()->company_uid !== $expense->company_uid) {
                return redirect()->route('admin.payroll.onetime-expenses')
                    ->with('verify', 'expense-updated')
                    ->with('status', 'danger')
                    ->with('message', 'You do not have permission to update this expense.');
            }

            $expense->update([
                'company_uid' => $request->company_uid,
                'purpose' => $request->purpose,
                'pay_to' => $request->pay_to,
                'amount' => $request->amount,
                'date' => $request->date,
                'description' => $request->description,
                'payment_status' => $request->payment_status,
            ]);

            return redirect()->route('admin.payroll.onetime-expenses')
                ->with('verify', 'expense-updated')
                ->with('status', 'success')
                ->with('message', 'One-time expense updated successfully.');
        } catch (\Exception $e) {
            \Log::error('One-time Expense Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('verify', 'expense-updated')
                ->with('status', 'danger')
                ->with('message', 'Error updating expense. Please try again.');
        }
    }

    public function delete($id)
    {
        try {
            $expense = OnetimeExpens::findOrFail($id);

            if (Auth::user()->role !== 'super-admin' && Auth::user()->company_uid !== $expense->company_uid) {
                return redirect()->route('admin.payroll.onetime-expenses')
                    ->with('verify', 'expense-updated')
                    ->with('status', 'danger')
                    ->with('message', 'You do not have permission to delete this expense.');
            }

            $expense->delete();

            return redirect()->route('admin.payroll.onetime-expenses')
                ->with('verify', 'expense-updated')
                ->with('status', 'success')
                ->with('message', 'One-time expense deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('One-time Expense Deletion Error: ' . $e->getMessage());
            return redirect()->route('admin.payroll.onetime-expenses')
                ->with('verify', 'expense-updated')
                ->with('status', 'danger')
                ->with('message', 'Error deleting expense. Please try again.');
        }
    }

    public function preview(Request $request)
    {
        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls',
                'company_uid' => Auth::user()->role === 'super-admin' ? 'required|string|exists:company_settings,company_uid' : 'nullable',
            ]);

            $user = Auth::user();

            // For regular users, always use their own company_uid
            // For superadmins, use the selected company_uid
            $company_uid = $user->role === 'super-admin'
                ? $request->company_uid
                : $user->company_uid;

            // Double-check that regular users can only use their own company_uid
            if ($user->role !== 'super-admin' && $request->has('company_uid') && $request->company_uid !== $user->company_uid) {
                \Log::warning('Regular user attempted to preview import for different company:', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'user_company_uid' => $user->company_uid,
                    'requested_company_uid' => $request->company_uid
                ]);

                // Force use of user's company_uid
                $company_uid = $user->company_uid;
            }

            // Log the file details
            \Log::info('Preview Excel file details:', [
                'original_name' => $request->file('excel_file')->getClientOriginalName(),
                'size' => $request->file('excel_file')->getSize(),
                'mime_type' => $request->file('excel_file')->getMimeType(),
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'user_company_uid' => $user->company_uid,
                'using_company_uid' => $company_uid
            ]);

            // Create a new import instance with preview mode enabled
            $import = new OnetimeExpensesImport($company_uid, true);

            // Import the file
            Excel::import($import, $request->file('excel_file'));

            // Get preview data
            $previewData = $import->getPreviewData();

            // Log the preview data summary
            \Log::info('Preview data summary:', [
                'total_rows' => $previewData['total_rows'] ?? 0,
                'valid_rows' => $previewData['valid_rows'] ?? 0,
                'error_rows' => $previewData['error_rows'] ?? 0,
                'data_count' => count($previewData['data'] ?? []),
                'data_sample' => !empty($previewData['data']) ? array_slice($previewData['data'], 0, 1) : []
            ]);

            return response()->json([
                'success' => true,
                'preview' => $previewData,
            ]);
        } catch (\Exception $e) {
            \Log::error('One-time expenses preview error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing file: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls',
                'company_uid' => Auth::user()->role === 'super-admin' ? 'required|string|exists:company_settings,company_uid' : 'nullable'
            ]);

            $user = Auth::user();

            // For regular users, always use their own company_uid
            // For superadmins, use the selected company_uid
            $company_uid = $user->role === 'super-admin'
                ? $request->company_uid
                : $user->company_uid;

            // Double-check that regular users can only use their own company_uid
            if ($user->role !== 'super-admin' && $request->has('company_uid') && $request->company_uid !== $user->company_uid) {
                \Log::warning('Regular user attempted to import for different company:', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'user_company_uid' => $user->company_uid,
                    'requested_company_uid' => $request->company_uid
                ]);

                // Force use of user's company_uid
                $company_uid = $user->company_uid;
            }

            // Log the file details
            \Log::info('Import Excel file details:', [
                'original_name' => $request->file('excel_file')->getClientOriginalName(),
                'size' => $request->file('excel_file')->getSize(),
                'mime_type' => $request->file('excel_file')->getMimeType(),
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'user_company_uid' => $user->company_uid,
                'using_company_uid' => $company_uid
            ]);

            // Create import instance for actual import
            $import = new OnetimeExpensesImport($company_uid);

            // Import the file
            Excel::import($import, $request->file('excel_file'));

            $errors = $import->getErrors();

            if (count($errors) > 0) {
                \Log::warning('Import completed with errors:', [
                    'error_count' => count($errors),
                    'errors' => $errors
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Some rows could not be imported.',
                    'errors' => $errors,
                ], 422);
            }

            \Log::info('Import completed successfully');

            return response()->json([
                'success' => true,
                'message' => 'Expenses imported successfully.',
            ]);

        } catch (\Exception $e) {
            \Log::error('One-time expenses import error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error importing file: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'payment_status' => ['required', 'string', 'in:pending,paid,cancelled'],
            ]);

            $expense = OnetimeExpens::findOrFail($id);

            // Check permission
            if (Auth::user()->role !== 'super-admin' && Auth::user()->company_uid !== $expense->company_uid) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this expense.',
                ], 403);
            }

            $expense->update([
                'payment_status' => $request->payment_status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully.',
            ]);

        } catch (\Exception $e) {
            \Log::error('One-time Expense Payment Status Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating payment status: ' . $e->getMessage(),
            ], 422);
        }
    }
}
