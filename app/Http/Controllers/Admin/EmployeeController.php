<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanySetting;
use App\Models\EmployeeSetup;
use App\Models\MonthlyExpens;
use App\Imports\MonthlyExpensesImport;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company_uid = $user->company_uid;

        // Get company information
        $my_company = CompanySetting::where('company_uid', $company_uid)->first();

        // Query builder for employees
        $query = User::with(['employeeSetup', 'company']);

        // If super-admin, show all users, otherwise filter by company
        if ($user->role !== 'super-admin') {
            $query->where('company_uid', $company_uid);
        }

        $my_employee = $query->get();

        return view('admin.payroll.employee.setup', compact('my_employee', 'my_company'));
    }

    public function setupConfig($id)
    {
        $user = Auth::user();
        $company_uid = $user->company_uid;

        // Get employee information with relationships
        $query = User::with(['employeeSetup', 'company'])->where('id', $id);

        // If not super-admin, restrict to company's employees
        if ($user->role !== 'super-admin') {
            $query->where('company_uid', $company_uid);
            $my_company = CompanySetting::where('company_uid', $company_uid)->firstOrFail();
        } else {
            // For super-admin, get the employee's company
            $my_employee = $query->firstOrFail();
            $my_company = CompanySetting::where('company_uid', $my_employee->company_uid)->firstOrFail();
        }

        $my_employee = $query->firstOrFail();

        return view('admin.payroll.employee.setup_config', compact('my_employee', 'my_company'));
    }

    public function Setupstore(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'company_uid' => 'required|exists:company_settings,company_uid',
            'due_date' => 'required|date',
            'salary' => 'required|numeric',
            'remarks' => 'nullable|string',
        ]);

        $employee = User::find($request->employee_id);
        $employee->EmployeeSetup()->create([
            'company_uid' => $request->company_uid,
            'due_date' => $request->due_date,
            'salary' => $request->salary,
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('admin.payroll.employee.setup')->with(['verify' => 'employee-setup-updated', 'status' => 'success', 'message' => 'Employee ' . $employee->first_name . ' ' . $employee->last_name . ' setup successfully']);
    }

    public function setupEdit($id)
    {
        $employee = User::find($id);
        $company_uid = $employee->company_uid;

        $employeeSetup = EmployeeSetup::where('employee_id', $id)->first();

        return view('admin.payroll.employee.setup_edit', compact('employee', 'employeeSetup'));
    }

    public function setupUpdate(Request $request, $id)
    {
        $request->validate([
            'company_uid' => 'required|exists:company_settings,company_uid',
            'due_date' => 'required|date',
            'salary' => 'required|numeric',
            'remarks' => 'nullable|string',
        ]);

        $employeeSetup = EmployeeSetup::where('employee_id', $id)->first();
        $employeeSetup->salary = $request->salary;
        $employeeSetup->remarks = $request->remarks;
        $employeeSetup->save();

        return redirect()->route('admin.payroll.employee.setup.edit', $id)->with(['verify' => 'employee-setup-updated', 'status' => 'success', 'message' => 'Employee ' . $employee->first_name . ' ' . $employee->last_name . ' Edited successfully']);
    }

    public function setupReset($id)
    {
        $employeeSetup = EmployeeSetup::where('employee_id', $id)->first();
        $employeeSetup->delete();

        $employee = User::findOrFail($id);

        return redirect()->route('admin.payroll.employee.setup')->with(['verify' => 'employee-setup-updated', 'status' => 'success', 'message' => 'Employee ' . $employee->first_name . ' ' . $employee->last_name . ' Reset successfully']);
    }

    public function monthlyExpenses()
    {
        $query = MonthlyExpens::with('company');
        $companies = [];

        if (Auth::user()->role === 'super-admin') {
            $companies = CompanySetting::all();
        } else {
            $query->where('company_uid', Auth::user()->company_uid);
        }

        $expenses = $query->latest('date')->paginate(10);

        return view('admin.payroll.monthly_expenses.index', compact('expenses', 'companies'));
    }

    public function createMonthlyExpenses()
    {
        $user = Auth::user();
        $companies = [];

        if ($user->role === 'super-admin') {
            $companies = CompanySetting::all();
        } else {
            $companies = CompanySetting::where('company_uid', $user->company_uid)->get();
        }

        return view('admin.payroll.monthly_expenses.create_expense', compact('companies'));
    }

    public function storeMonthlyExpenses(Request $request)
    {
        $request->validate([
            'company_uid' => ['required', 'string', 'exists:company_settings,company_uid'],
            'purpose' => ['required', 'string', 'max:255'],
            'pay_to' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        try {
            MonthlyExpens::create([
                'company_uid' => $request->company_uid,
                'purpose' => $request->purpose,
                'pay_to' => $request->pay_to,
                'amount' => $request->amount,
                'date' => $request->date,
                'description' => $request->description ?? null,
            ]);

            return redirect()->route('admin.payroll.monthly-expenses')
                ->with('verify', 'expense-updated')
                ->with('status', 'success')
                ->with('message', 'Monthly expense created successfully.');
        } catch (\Exception $e) {
            \Log::error('Monthly Expense Creation Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('verify', 'expense-updated')
                ->with('status', 'danger')
                ->with('message', 'Error creating monthly expense. Please try again.');
        }
    }

    public function deleteMonthlyExpenses($id)
    {
        try {
            $expense = MonthlyExpens::findOrFail($id);

            // Check if user has permission to delete this expense
            if (Auth::user()->role !== 'super-admin' && Auth::user()->company_uid !== $expense->company_uid) {
                return redirect()->route('admin.payroll.monthly-expenses')
                    ->with('verify', 'expense-updated')
                    ->with('status', 'danger')
                    ->with('message', 'You do not have permission to delete this expense.');
            }

            $expense->delete();

            return redirect()->route('admin.payroll.monthly-expenses')
                ->with('verify', 'expense-updated')
                ->with('status', 'success')
                ->with('message', 'Monthly expense deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Monthly Expense Deletion Error: ' . $e->getMessage());
            return redirect()->route('admin.payroll.monthly-expenses')
                ->with('verify', 'expense-updated')
                ->with('status', 'danger')
                ->with('message', 'Error deleting monthly expense. Please try again.');
        }
    }

    public function monthlyExpensesEdit($id)
    {
        try {
            $expense = MonthlyExpens::findOrFail($id);

            // Check if user has permission to edit this expense
            if (Auth::user()->role !== 'super-admin' && Auth::user()->company_uid !== $expense->company_uid) {
                return redirect()->route('admin.payroll.monthly-expenses')
                    ->with('verify', 'expense-updated')
                    ->with('status', 'danger')
                    ->with('message', 'You do not have permission to edit this expense.');
            }

            // Get companies for dropdown if user is super-admin
            $companies = [];
            if (Auth::user()->role === 'super-admin') {
                $companies = CompanySetting::all();
            } else {
                $companies = CompanySetting::where('company_uid', Auth::user()->company_uid)->get();
            }

            return view('admin.payroll.monthly_expenses.edit', compact('expense', 'companies'));
        } catch (\Exception $e) {
            \Log::error('Monthly Expense Edit Error: ' . $e->getMessage());
            return redirect()->route('admin.payroll.monthly-expenses')
                ->with('verify', 'expense-updated')
                ->with('status', 'danger')
                ->with('message', 'Error accessing expense. Please try again.');
        }
    }

    public function monthlyExpensesUpdate(Request $request, $id)
    {
        $request->validate([
            'company_uid' => ['required', 'string', 'exists:company_settings,company_uid'],
            'purpose' => ['required', 'string', 'max:255'],
            'pay_to' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        try {
            $expense = MonthlyExpens::findOrFail($id);

            // Check if user has permission to update this expense
            if (Auth::user()->role !== 'super-admin' && Auth::user()->company_uid !== $expense->company_uid) {
                return redirect()->route('admin.payroll.monthly-expenses')
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
            ]);

            return redirect()->route('admin.payroll.monthly-expenses')
                ->with('verify', 'expense-updated')
                ->with('status', 'success')
                ->with('message', 'Monthly expense updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Monthly Expense Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('verify', 'expense-updated')
                ->with('status', 'danger')
                ->with('message', 'Error updating expense. Please try again.');
        }
    }

    public function monthlyExpensesPreview(Request $request)
    {
        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls',
                'company_uid' => Auth::user()->role === 'super-admin' ? 'required|string|exists:company_settings,company_uid' : 'nullable',
            ]);

            $company_uid = Auth::user()->role === 'super-admin' ? $request->company_uid : Auth::user()->company_uid;
            $import = new MonthlyExpensesImport($company_uid, true);

            Excel::import($import, $request->file('excel_file'));

            return response()->json([
                'success' => true,
                'preview' => $import->getPreviewData(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Monthly expenses preview error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing file: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function monthlyExpensesImport(Request $request)
    {
        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls',
                'company_uid' => Auth::user()->role === 'super-admin' ? 'required|string|exists:company_settings,company_uid' : 'nullable',
            ]);

            $company_uid = Auth::user()->role === 'super-admin' ? $request->company_uid : Auth::user()->company_uid;
            $import = new MonthlyExpensesImport($company_uid, false);

            Excel::import($import, $request->file('excel_file'));

            if (count($import->getErrors()) > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some rows could not be imported.',
                    'errors' => $import->getErrors(),
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Monthly expenses imported successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Monthly expenses import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error importing file: ' . $e->getMessage(),
            ], 422);
        }
    }
}
