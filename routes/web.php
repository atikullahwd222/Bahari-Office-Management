<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\OnetimeExpensesController;
use App\Http\Controllers\Admin\PayrollOverviewController;
use App\Http\Controllers\Server\CommandController;
use App\Http\Controllers\Server\ServerController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/update-photo', [ProfileController::class, 'update_photo'])->name('profile.update.photo');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('profile.settings');
});

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/company-branding', [CompanyController::class, 'companySettings'])->name('admin.company.branding');
    Route::post('/company-branding/logo', [CompanyController::class, 'companyLogoUpdate'])->name('admin.company.logo.update');
    Route::post('/company-branding/favicon', [CompanyController::class, 'companyFaviconUpdate'])->name('admin.company.favicon.update');
    Route::get('/company-branding/company-info', [CompanyController::class, 'companyInfo'])->name('admin.company.company-info');
    Route::post('/company-branding/company-info/{uid}', [CompanyController::class, 'companyInfoUpdate'])->name('admin.company-info.update');

    Route::get('/employee-setup', [EmployeeController::class, 'index'])->name('admin.payroll.employee.setup');
    Route::get('/employee-setup/config/{id}', [EmployeeController::class, 'setupConfig'])->name('admin.payroll.employee.setup.config');
    Route::post('/employee-setup/store', [EmployeeController::class, 'setupStore'])->name('admin.payroll.employee.setup.store');
    Route::get('/employee-setup/edit/{id}', [EmployeeController::class, 'setupEdit'])->name('admin.payroll.employee.setup.edit');
    Route::post('/employee-setup/update/{id}', [EmployeeController::class, 'setupUpdate'])->name('admin.payroll.employee.setup.update');
    Route::get('/employee-setup/reset/{id}', [EmployeeController::class, 'setupReset'])->name('admin.payroll.employee.setup.reset');

    Route::get('/monthly-expenses', [EmployeeController::class, 'monthlyExpenses'])->name('admin.payroll.monthly-expenses');
    Route::get('/monthly-expenses/create', [EmployeeController::class, 'createMonthlyExpenses'])->name('admin.payroll.monthly-expenses.create');
    Route::post('/monthly-expenses', [EmployeeController::class, 'storeMonthlyExpenses'])->name('admin.payroll.monthly-expenses.store');
    Route::get('/monthly-expenses/{id}/edit', [EmployeeController::class, 'monthlyExpensesEdit'])->name('admin.payroll.monthly-expenses.edit');
    Route::put('/monthly-expenses/{id}', [EmployeeController::class, 'monthlyExpensesUpdate'])->name('admin.payroll.monthly-expenses.update');
    Route::delete('/monthly-expenses/{id}', [EmployeeController::class, 'deleteMonthlyExpenses'])->name('admin.payroll.monthly-expenses.delete');
    Route::post('/monthly-expenses/preview', [EmployeeController::class, 'monthlyExpensesPreview'])->name('admin.payroll.monthly-expenses.preview');
    Route::post('/monthly-expenses/import', [EmployeeController::class, 'monthlyExpensesImport'])->name('admin.payroll.monthly-expenses.import');

    Route::get('/onetime-expenses', [OnetimeExpensesController::class, 'index'])->name('admin.payroll.onetime-expenses');
    Route::get('/onetime-expenses/create', [OnetimeExpensesController::class, 'create'])->name('admin.payroll.onetime-expenses.create');
    Route::post('/onetime-expenses', [OnetimeExpensesController::class, 'store'])->name('admin.payroll.onetime-expenses.store');
    Route::get('/onetime-expenses/{id}/edit', [OnetimeExpensesController::class, 'edit'])->name('admin.payroll.onetime-expenses.edit');
    Route::put('/onetime-expenses/{id}', [OnetimeExpensesController::class, 'update'])->name('admin.payroll.onetime-expenses.update');
    Route::delete('/onetime-expenses/{id}', [OnetimeExpensesController::class, 'delete'])->name('admin.payroll.onetime-expenses.delete');
    Route::post('/onetime-expenses/preview', [OnetimeExpensesController::class, 'preview'])->name('admin.payroll.onetime-expenses.preview');
    Route::post('/onetime-expenses/import', [OnetimeExpensesController::class, 'import'])->name('admin.payroll.onetime-expenses.import');
    Route::post('/onetime-expenses/{id}/update-status', [OnetimeExpensesController::class, 'updateStatus'])->name('admin.payroll.onetime-expenses.update-status');

    Route::get('/company-users', [AdminController::class, 'companyUsers'])->name('admin.company.users');
    Route::get('/company-user/create', [AdminController::class, 'createUser'])->name('admin.company.user.create');
    Route::post('/company-user/store', [AdminController::class, 'storeUser'])->name('admin.company.user.store');
    Route::get('/company-user/{id}', [AdminController::class, 'editUser'])->name('admin.company.user.edit');
    Route::post('/company-user/{id}/photo', [AdminController::class, 'updatePhoto'])->name('user.profile.update.photo');
    Route::post('/company-user/{id}/update', [AdminController::class, 'profileUpdate'])->name('user.profile.update');
    Route::post('/company-user/{id}/delete', [AdminController::class, 'destroyUser'])->name('user.profile.delete');

    Route::post('/admin/users/import', [AdminController::class, 'importUsers'])->name('admin.users.import');

    // Route::get('/payroll/overview', [PayrollOverviewController::class, 'index'])->name('payroll.overview');
    // Route::get('/payroll/generate', [PayrollOverviewController::class, 'generatePayroll'])->name('payroll.generate');
    Route::get('/payroll', [PayrollOverviewController::class, 'index'])->name('admin.payroll.index');
    Route::post('/payroll/generate', [PayrollOverviewController::class, 'generatePayroll'])->name('admin.payroll.generate');
    Route::post('/payroll/{id}/mark-as-paid', [PayrollOverviewController::class, 'markAsPaid'])->name('admin.payroll.mark-as-paid');
    Route::post('/payroll/bulk-mark-as-paid', [PayrollOverviewController::class, 'bulkMarkAsPaid'])->name('admin.payroll.bulk-mark-as-paid');
    
    Route::get('/server/commands', [CommandController::class, 'index'])->name('admin.server.commands');
    Route::get('/server/commands/create', [CommandController::class, 'create'])->name('admin.server.commands.create');
    Route::post('/server/commands/store', [CommandController::class, 'store'])->name('admin.server.commands.store');
    Route::get('/server/commands/edit/{id}', [CommandController::class, 'edit'])->name('admin.server.commands.edit');
    Route::post('/server/commands/update/{id}', [CommandController::class, 'update'])->name('admin.server.commands.update');
    Route::delete('/server/commands/delete/{id}', [CommandController::class, 'destroy'])->name('admin.server.commands.delete');
    
});

// Server Managment routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/server', [ServerController::class, 'index'])->name('admin.server.index');
});

Route::get('/payroll/cron', [PayrollOverviewController::class, 'payrollCron'])->name('admin.payroll.payrollCron');

Route::post('/admin/payroll/settings/update', [PayrollOverviewController::class, 'updateSettings'])
    ->name('admin.payroll.settings.update')
    ->middleware(['auth', 'superadmin']);

require __DIR__.'/auth.php';
