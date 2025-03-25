<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmployeeSetup;
use App\Models\MonthlyExpens;
use App\Models\OnetimeExpens;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\PayrollPaidNotification;

class PayrollOverviewController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        // Get selected month and year or default to current month
        $selectedMonth = $request->get('month', $today->format('Y-m'));
        $selectedDate = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();

        // Base query for payroll entries
        $query = Payroll::with([
            'company',
            'employeeSetup.employee',
            'monthlyExpense',
            'onetimeExpense'
        ]);

        // Restrict regular users to their own company's data
        if ($user->role !== 'super-admin') {
            $query->where('company_uid', $user->company_uid);
        }

        // Get pending payrolls (not yet paid)
        $pendingPayroll = (clone $query)
            ->where('status', 'pending')
            ->whereBetween('due_date', [$today->startOfMonth()->toDateString(), $today->endOfMonth()->toDateString()])
            ->orderBy('due_date')
            ->get();


        // Get paid payrolls (already paid)
        $paidPayroll = (clone $query)
            ->where('status', 'paid')
            ->whereYear('updated_at', $selectedDate->year)
            ->whereMonth('updated_at', $selectedDate->month)
            ->orderByDesc('updated_at')
            ->get();

        // Ensure type 'expense' is included
        $paidPayroll = $paidPayroll->filter(function ($payroll) {
            return in_array($payroll->type, ['expense', 'onetime-expense', 'salary']);
        });

        // dd($pendingPayroll->toArray(), $paidPayroll->toArray());
        // Generate months list for dropdown (last 12 months)
        $months = collect([]);
        for ($i = 0; $i < 12; $i++) {
            $date = $today->copy()->subMonths($i);
            $months->push([
                'value' => $date->format('Y-m'),
                'label' => $date->format('F Y')
            ]);
        }

        $totalMonthlyExpense = (clone $query)
        ->where('type', 'expense')
        ->where('status', 'paid')
        ->whereBetween('due_date', [$today->startOfMonth()->toDateString(), $today->endOfMonth()->toDateString()])
        ->get()
        ->sum('amount');
        
        $totalOnetimeExpense = (clone $query)
        ->where('type', 'onetime-expense')
        ->where('status', 'paid')
        ->whereBetween('due_date', [$today->startOfMonth()->toDateString(), $today->endOfMonth()->toDateString()])
        ->get()
        ->sum('amount');
    
        $totalSalary = (clone $query)
        ->where('type', 'salary')
        ->where('status', 'paid')
        ->whereBetween('due_date', [$today->startOfMonth()->toDateString(), $today->endOfMonth()->toDateString()])
        ->get()
        ->sum('amount');

        // Get settings for superadmin
        $settings = $this->getPayrollSettings();
    
        \Log::info('Payroll Query Details:', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_company_uid' => $user->company_uid,
            'pending_count' => $pendingPayroll->count(),
            'paid_count' => $paidPayroll->count(),
            'selected_month' => $selectedMonth
        ]);
    
        // return view('admin.payroll.index', compact('pendingPayroll', 'paidPayroll', 'months', 'selectedMonth', 'settings'));
        return view('admin.payroll.index', compact(
            'pendingPayroll',
            'paidPayroll',
            'months',
            'selectedMonth',
            'settings',
            'totalMonthlyExpense',
            'totalOnetimeExpense',
            'totalSalary'
        ));
    }
    
    public function payrollCron(){
        try {
            $today = Carbon::today();
            $startOfMonth = $today->copy()->startOfMonth();
            $endOfMonth = $today->copy()->endOfMonth();
            $generatedCount = 0;
            $reportData = [
                'oneTimeExpenses' => 0,
                'monthlyExpenses' => 0,
                'salaries' => 0,
                'totalAmount' => 0
            ];

            \Log::info('Starting Payroll Generation:', [
                'month' => $today->format('F Y')
            ]);

            // [Previous one-time expenses code remains the same until the foreach]
            foreach ($oneTimeExpenses as $expense) {
                try {
                    DB::beginTransaction();
                    // [Previous code remains the same]
                    if (!$existingPayroll) {
                        Payroll::create([/* same creation code */]);
                        $generatedCount++;
                        $reportData['oneTimeExpenses']++;
                        $reportData['totalAmount'] += $expense->amount;
                        $expense->date = Carbon::parse($expense->date)->addMonth();
                        $expense->save();
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    // [Same error handling]
                }
            }

            // [Previous monthly expenses code remains the same until the foreach]
            foreach ($monthlyExpenses as $expense) {
                try {
                    DB::beginTransaction();
                    // [Previous code remains the same]
                    if (!$existingPayroll) {
                        Payroll::create([/* same creation code */]);
                        $generatedCount++;
                        $reportData['monthlyExpenses']++;
                        $reportData['totalAmount'] += $expense->amount;
                        $expense->date = Carbon::parse($expense->date)->addMonth();
                        $expense->save();
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    // [Same error handling]
                }
            }

            // [Previous employee salaries code remains the same until the foreach]
            foreach ($employees as $employee) {
                try {
                    DB::beginTransaction();
                    // [Previous code remains the same]
                    $payroll = Payroll::create([/* same creation code */]);
                    $generatedCount++;
                    $reportData['salaries']++;
                    $reportData['totalAmount'] += $employee->salary;
                    $employee->due_date = Carbon::parse($employee->due_date)->addMonth();
                    $employee->save();
                    DB::commit();
                } catch (\Exception $e) {
                    // [Same error handling]
                }
            }

            // Send email report
            if ($generatedCount > 0) {
                Mail::to(config('mail.admin_email'))->send(new PayrollGenerationReport(
                    $reportData,
                    $today->format('F Y'),
                    $generatedCount
                ));
            }

            return response()->json([
                'status' => 'success',
                'message' => "Generated $generatedCount payroll entries and sent report"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payroll Generation Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generatePayroll()
    {
        try {
            $user = Auth::user();
            DB::beginTransaction();

            $today = Carbon::today();
            $startOfMonth = $today->copy()->startOfMonth();
            $endOfMonth = $today->copy()->endOfMonth();
            $generatedCount = 0;

            \Log::info('Starting Payroll Generation:', [
                'user_id' => $user->id,
                'company_uid' => $user->company_uid,
                'month' => $today->format('F Y')
            ]);

            // 1. Process Monthly Expenses
            // Process Monthly Expenses
            $monthlyExpensesQuery = MonthlyExpens::query();

            if ($user->role !== 'super-admin') {
                $monthlyExpensesQuery->where('company_uid', $user->company_uid);
            }

            $monthlyExpenses = $monthlyExpensesQuery
                ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('date', [$startOfMonth, $endOfMonth])
                        ->orWhere(function ($q) use ($startOfMonth) {
                            $q->where('date', '<', $startOfMonth)
                                ->where('payment_status', '!=', 'paid');
                        });
                })
                ->whereDoesntHave('payroll', function ($query) use ($today) {
                    $query->whereMonth('due_date', $today->month)
                        ->whereYear('due_date', $today->year);
                })
                ->get();

            foreach ($monthlyExpenses as $expense) {
                try {
                    DB::beginTransaction();

                    $existingPayroll = Payroll::where('reference_id', $expense->id)
                        ->where('type', 'expense')
                        ->whereMonth('due_date', $today->month)
                        ->whereYear('due_date', $today->year)
                        ->exists();

                    if (!$existingPayroll) {
                        Payroll::create([
                            'company_uid'  => $expense->company_uid,
                            'type'         => 'expense',
                            'reference_id' => $expense->id,
                            'amount'       => $expense->amount,
                            'due_date'     => $expense->date,
                            'status'       => 'pending'
                        ]);
                        $generatedCount++;
                        // Update next month's date
                        $expense->date = Carbon::parse($expense->date)->addMonth();
                        $expense->save();
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Error processing monthly expense:', [
                        'expense_id' => $expense->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            # Monthly Expesnes Handle
            // Process One-Time Expenses
            $oneTimeExpenseQuery = OnetimeExpens::query();

            if ($user->role !== 'super-admin') {
                $oneTimeExpenseQuery->where('company_uid', $user->company_uid);
            }

            // Fetch one-time expenses that do not exist in payroll yet
                $oneTimeExpenses = $oneTimeExpenseQuery
                ->where('payment_status', 'pending')
                ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('date', [$startOfMonth, $endOfMonth])
                        ->orWhere(function ($q) use ($startOfMonth) {
                            $q->where('date', '<', $startOfMonth)
                                ->where('payment_status', 'pending');
                        });
                })
                ->whereDoesntHave('payroll')
                ->get();

                DB::beginTransaction();
                foreach ($oneTimeExpenses as $expense) {
                    try {

                        $existingPayroll = Payroll::where('reference_id', $expense->id)
                        ->where('type', 'onetime-expense')
                        ->whereMonth('due_date', $today->month)
                        ->whereYear('due_date', $today->year)
                        ->exists();

                        if(!$existingPayroll) {
                            $payroll = Payroll::create([
                                'company_uid'  => $expense->company_uid,
                                'type'         => 'onetime-expense',
                                'reference_id' => $expense->id,
                                'amount'       => $expense->amount,
                                'due_date'     => $expense->date,
                                'status'       => 'pending'
                            ]);
                            
                            $generatedCount++;
                            // $expense->save();
                            if ($payroll) {
                                // Update the expense date to next month
                                $expense->date = Carbon::parse($expense->date)->addMonth();
                                $expense->save();
                            }
                        }
                        
                        \Log::info('Created One-time Expense Payroll:', [
                            'expense_id' => $expense->id,
                            'amount' => $expense->amount,
                            'due_date' => $expense->date
                        ]);

                        DB::commit();
                    } catch (\Exception $e) {
                        \Log::error('Error processing one-time expense:', [
                            'expense_id' => $expense->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                

            // 2. Process Employee Salaries
            $employeesQuery = EmployeeSetup::whereBetween('due_date', [$startOfMonth, $endOfMonth]);
            if ($user->role !== 'super-admin') {
                $employeesQuery->where('company_uid', $user->company_uid);
            }

            $employees = $employeesQuery
                ->whereDoesntHave('payroll', function($query) use ($today) {
                    $query->whereMonth('due_date', $today->month)
                          ->whereYear('due_date', $today->year);
                })
                ->get();

            foreach ($employees as $employee) {
                try {
                    $payroll = Payroll::create([
                        'company_uid'  => $employee->company_uid,
                        'type'         => 'salary',
                        'reference_id' => $employee->id,
                        'amount'       => $employee->salary,
                        'due_date'     => $employee->due_date,
                        'status'       => 'pending'
                    ]);

                    // Update next month's due date
                    $oldDate = $employee->due_date;
                    $employee->due_date = Carbon::parse($employee->due_date)->addMonth();
                    $employee->save();

                    $generatedCount++;

                    \Log::info('Created Salary Payroll:', [
                        'payroll_id' => $payroll->id,
                        'employee_id' => $employee->id,
                        'old_date' => $oldDate,
                        'new_date' => $employee->due_date
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error processing employee salary:', [
                        'employee_id' => $employee->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    continue;
                }
            }

            DB::commit();

            \Log::info('Payroll Generation Complete:', [
                'user_id' => $user->id,
                'total_generated' => $generatedCount,
                'month' => $today->format('F Y'),
                'monthly_expenses' => $monthlyExpenses->count(),
                'salaries' => $employees->count(),
                'onetime_expenses' => $oneTimeExpenses->count()
            ]);

            if ($generatedCount > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => $generatedCount . ' payroll entries generated successfully for ' . $today->format('F Y')
                ], 200);
            } else {
                return response()->json([
                    'status' => 'info',
                    'message' => 'No new payroll entries to generate for ' . $today->format('F Y')
                ], 200);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payroll Generation Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markAsPaid($id)
    {
        try {
            $user = Auth::user();
            DB::beginTransaction();

            // Find the payroll entry
            $payrollQuery = Payroll::where('id', $id);

            // Regular users can only mark their own company's payrolls as paid
            if ($user->role !== 'super-admin') {
                $payrollQuery->where('company_uid', $user->company_uid);
            }

            $payroll = $payrollQuery->first();

            if (!$payroll) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payroll entry not found or you do not have permission to update it'
                ], 404);
            }

            // Update payroll status
            $payroll->status = 'paid';
            $payroll->paid_date = Carbon::now();
            $payroll->save();

            // Update one-time expense status if applicable
            if ($payroll->type === 'expense') {
                $expense = OnetimeExpens::find($payroll->reference_id);
                if ($expense) {
                    $oldStatus = $expense->payment_status;
                    $expense->payment_status = 'paid';
                    $expense->save();

                    \Log::info('Updated one-time expense payment status:', [
                        'expense_id' => $expense->id,
                        'payroll_id' => $payroll->id,
                        'old_status' => $oldStatus,
                        'new_status' => 'paid'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment marked as paid successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Mark Payroll as Paid Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark payment as paid: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkMarkAsPaid(Request $request)
    {
        try {
            $user = Auth::user();
            $payrollIds = $request->input('payroll_ids', []);

            if (empty($payrollIds)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No payroll entries selected'
                ], 400);
            }

            // Get the payrolls to update
            $payrollsQuery = Payroll::whereIn('id', $payrollIds);
            if ($user->role !== 'super-admin') {
                $payrollsQuery->where('company_uid', $user->company_uid);
            }

            $payrolls = $payrollsQuery->get();

            if ($payrolls->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No valid payroll entries found to update'
                ], 404);
            }

            DB::beginTransaction();

            $totalAmount = 0;
            foreach ($payrolls as $payroll) {
                // Update payroll status
                $payroll->status = 'paid';
                $payroll->paid_date = Carbon::now();
                $payroll->save();
                $totalAmount += $payroll->amount;

                // Update one-time expense status if applicable
                if ($payroll->type === 'expense') {
                    $expense = OnetimeExpens::find($payroll->reference_id);
                    if ($expense) {
                        $oldStatus = $expense->payment_status;
                        $expense->payment_status = 'paid';
                        $expense->save();

                        \Log::info('Updated one-time expense payment status:', [
                            'expense_id' => $expense->id,
                            'payroll_id' => $payroll->id,
                            'old_status' => $oldStatus,
                            'new_status' => 'paid'
                        ]);
                    }
                }
            }

            // Send email notification
            foreach ($payrolls as $payroll) {
                $company = $payroll->company_uid;
                $user = User::where('company_uid', $company)->first();
                
                if ($user) {
                    try {
                        $mailData = [
                            'name' => $user->name,
                            'month' => $payroll->due_date,
                            'amount' => number_format($payroll->amount, 2),
                            'total_amount' => number_format($totalAmount, 2),
                            'payment_date' => Carbon::now()->format('Y-m-d'),
                            'payment_method' => 'Bank Transfer',
                            'payroll_details' => [
                                'type' => $payroll->type,
                                'reference_id' => $payroll->reference_id,
                                'due_date' => $payroll->due_date,
                                'status' => $payroll->status
                            ]
                        ];

                        Mail::to($user->email)
                            ->send(new PayrollPaidNotification($mailData));

                        \Log::info('Payroll notification sent', [
                            'company_uid' => $company,
                            'email' => $user->email,
                            'total_amount' => $totalAmount,
                            'payroll_id' => $payroll->id
                        ]);

                    } catch (\Exception $e) {
                        \Log::error('Email send failed:', [
                            'error' => $e->getMessage(),
                            'payroll_id' => $payroll->id
                        ]);
                    }
                }
            }
            

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => count($payrolls) . ' payroll entries marked as paid'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk Mark Payroll as Paid Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark payrolls as paid: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateSettings(Request $request)
    {
        try {
            if (!auth()->user()->is_superadmin) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $request->validate([
                'days_to_show' => 'required|integer|min:1|max:60',
                'auto_generate' => 'required|boolean',
                'notification_days' => 'required|integer|min:1|max:10',
                'currency' => 'required|string|max:10',
                'weekend_days' => 'required|array',
                'weekend_days.*' => 'required|integer|min:0|max:6'
            ]);

            $settings = [
                'days_to_show' => $request->days_to_show,
                'auto_generate' => $request->auto_generate,
                'notification_days' => $request->notification_days,
                'currency' => $request->currency,
                'weekend_days' => $request->weekend_days,
                'updated_at' => now()->toDateTimeString(),
                'updated_by' => auth()->id()
            ];

            Cache::put('payroll_settings', $settings, now()->addYear());

            return response()->json([
                'status' => 'success',
                'message' => 'Settings updated successfully',
                'settings' => $settings
            ]);

        } catch (\Exception $e) {
            \Log::error('Payroll Settings Update Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getPayrollSettings()
    {
        return Cache::remember('payroll_settings', now()->addDay(), function () {
            return [
                'days_to_show' => 10,
                'auto_generate' => false,
                'notification_days' => 3,
                'currency' => 'BDT',
                'weekend_days' => [5, 6], // Friday and Saturday
                'updated_at' => now()->toDateTimeString(),
                'updated_by' => auth()->id()
            ];
        });
    }
}
