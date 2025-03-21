<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    @if (session('verify') === 'profile-updated')
        <div class="alert alert-{{ session('status') }} alert-dismissible text-dark" role="alert">
            <b>{{ session('message') }}</b>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">

        <div class="row">
            <!-- Header Section with a 6 Column width -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Users</h5>
                    </div>
                </div>
            </div>

            <div class="col-md-6 d-flex justify-content-end gap-3">
                @if (auth()->user()->role == 'super-admin')
                    <form method="GET" action="{{ route('admin.company.users') }}" class="d-flex">
                        <select name="company_name" class="form-select align-self-center" onchange="this.form.submit()" style="height: 40px;">
                            <option value="" disabled selected>Select Company</option>
                            @foreach ($companies as $comp)
                                <option value="{{ $comp->company_name }}"
                                    {{ request('company_name') == $comp->company_name ? 'selected' : '' }}>
                                    {{ $comp->company_name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @else
                    @php
                        $company_name = auth()->user()->company_uid;
                    @endphp
                @endif

                <!-- Import Excel Button -->
                <button type="button" class="btn btn-success align-self-center" data-bs-toggle="modal" data-bs-target="#importModal">
                    Import Users
                </button>

                <a href="{{ route('admin.company.user.create') }}" class="btn btn-primary align-self-center">
                    Add User
                </a>
            </div>
        </div>

        <!-- Import Modal -->
        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Users from Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            @if (auth()->user()->role == 'super-admin')
                                <div class="mb-3">
                                    <label for="company_uid" class="form-label">Select Company</label>
                                    <select name="company_uid" class="form-select" required>
                                        @foreach ($companies as $comp)
                                            <option value="{{ $comp->company_uid }}">{{ $comp->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label for="excel_file" class="form-label">Excel File</label>
                                <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                            </div>
                            <div class="alert alert-info">
                                <h6>Excel File Format:</h6>
                                <p class="mb-0">The Excel file should have the following columns:</p>
                                <ul class="mb-0">
                                    <li>first_name (required)</li>
                                    <li>last_name (required)</li>
                                    <li>email (required)</li>
                                    <li>phone (required)</li>
                                    <li>address (optional)</li>
                                    <li>city (optional)</li>
                                    <li>state (optional)</li>
                                    <li>role (optional - defaults to 'user' if not specified)</li>
                                </ul>
                                <p class="mt-2 mb-0"><small>Notes:</small></p>
                                <ul class="mb-0">
                                    <li><small>Default password will be set to 'password123'</small></li>
                                    <li><small>Valid roles are: user, admin</small></li>
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Import Users</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <table class="table">
            <style>
                .usertable th {
                    color: white !important;
                }
            </style>
            <thead class="table-dark usertable">
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Company</th>
                    <th>Avatar</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach ($employees as $employee)
                    <tr>
                        <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
                        <td>{{ $employee->email }}</td>
                        <td>{{ $employee->company ? $employee->company->company_name : 'No Company' }}</td>
                        <td>
                            <img src="{{ asset($employee->profile_photo) }}" alt="Avatar"
                                 class="rounded-circle" style="width: 50px; height: 50px;" />
                        </td>
                        <td>
                            <span class="badge bg-label-{{ $employee->role == 'admin' || $employee->role == 'super-admin' ? 'danger' : 'primary' }} me-1">
                                {{ $employee->role }}
                            </span>

                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.company.user.edit', $employee->id) }}"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                    <form id="delete-form-{{ $employee->id }}" action="{{ route('user.profile.delete', $employee->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="button" class="dropdown-item text-danger delete-user"
                                                data-user-id="{{ $employee->id }}"
                                                data-user-name="{{ $employee->first_name }} {{ $employee->last_name }}">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle delete button clicks
            document.querySelectorAll('.delete-user').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const userId = this.dataset.userId;
                    const userName = this.dataset.userName;

                    showDeleteConfirmation({
                        title: 'Delete User',
                        text: `Are you sure you want to delete ${userName}?`,
                        confirmButtonText: 'Yes, delete user!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById(`delete-form-${userId}`);
                            if (form) {
                                form.submit();
                            }
                        }
                    });
                });
            });

            // Show success/error message if exists
            @if(session('verify') === 'profile-updated')
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                Toast.fire({
                    icon: '{{ session('status') }}',
                    title: '{{ session('message') }}'
                });
            @endif
        });
    </script>
    @endpush
</x-app-layout>
