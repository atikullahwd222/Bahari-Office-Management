<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Employee Setup') }}
        </h2>
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
                    <i class="bx bx-user-plus me-2"></i> Employee Selary Setup
                </h5>
                {{-- @if(Auth::user()->role === 'super-admin')
                    <span class="badge bg-danger">Super Admin Mode</span>
                @endif --}}
            </div>

            <div class="card-body">
                <div class="table-responsive">
                <table class="table mt-3">
                    <style>
                        .usertable th {
                            color: white !important;
                        }
                    </style>
                    <thead class="table-dark usertable text-center">
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th>Selary</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach($my_employee as $employee)
                            <tr>
                                <td>{{ $employee->id }}</td>
                                <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
                                <td>{{ $employee->email }}</td>
                                <td>
                                    @if(Auth::user()->role === 'super-admin')
                                        {{ $employee->company->company_name ?? 'N/A' }}
                                    @else
                                        {{ $my_company->company_name }}
                                    @endif
                                </td>
                                <td>
                                    @if($employee->employeeSetup)
                                        {{ $employee->employeeSetup->salary }} BDT
                                    @else
                                        <span class="badge bg-label-danger">Not Setup</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            @if($employee->employeeSetup)
                                                <a class="dropdown-item" href="{{ route('admin.payroll.employee.setup.edit', $employee->id) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                                <a class="dropdown-item text-danger" href="{{ route('admin.payroll.employee.setup.reset', $employee->id) }}">
                                                    <i class="bx bx-trash me-1"></i> Reset
                                                </a>
                                            @else
                                                <a href="{{ route('admin.payroll.employee.setup.config', $employee->id) }}" class="dropdown-item text-dark">
                                                    <i class="bx bx-cog me-1"></i> Setup
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@section('scripts')

@endsection
