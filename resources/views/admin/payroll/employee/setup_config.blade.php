<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Employee Setup Configuration') }}
            </h2>
            <a href="{{ route('admin.payroll.employee.setup') }}" class="btn btn-outline-primary btn-sm">
                <i class="bx bx-arrow-back me-1"></i> Back to List
            </a>
        </div>
    </x-slot>

    @if (session('verify') === 'employee-setup-updated')
        <div class="alert alert-{{ session('status') }} alert-dismissible text-dark" role="alert">
            <b>{{ session('message') }}</b>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 text-white">
                    <i class="bx bx-user-plus me-2"></i> Employee Setup Configuration
                </h5>
                {{-- @if(Auth::user()->role === 'super-admin')
                    <span class="badge bg-danger">Super Admin Mode</span>
                @endif --}}
            </div>
            <div class="card-body">
                <form id="employeeSetupForm" action="{{ route('admin.payroll.employee.setup.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $my_employee->id }}">
                    <input type="hidden" name="company_uid" value="{{ $my_company->company_uid }}">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="employee_name" class="form-label text-muted">Employee Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" class="form-control bg-light" id="employee_name" readonly
                                       value="{{ $my_employee->first_name }} {{ $my_employee->last_name }}">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="company_name" class="form-label text-muted">Company Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-buildings"></i></span>
                                <input type="text" class="form-control bg-light" id="company_name" readonly
                                       value="{{ $my_company->company_name }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label text-muted">Joining Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                <input type="date" class="form-control" id="due_date" name="due_date" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="salary" class="form-label text-muted">Salary</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-dollar"></i></span>
                                <input type="number" step="0.01" class="form-control" id="salary" name="salary" required
                                       placeholder="Enter salary amount">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="remarks" class="form-label text-muted">Remarks</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-comment"></i></span>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3"
                                      placeholder="Enter any additional remarks"></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.payroll.employee.setup') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-x me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-check me-1"></i> Save Setup
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>
