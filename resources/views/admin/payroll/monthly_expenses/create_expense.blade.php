<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Monthly Expense') }}
            </h2>
            <a href="{{ route('admin.payroll.monthly-expenses') }}" class="btn btn-outline-primary">
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
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="card-title mb-0 text-white">
                    <i class="bx bx-money me-2"></i> New Monthly Expense
                </h5>
                @if(Auth::user()->role === 'super-admin')
                    <span class="badge bg-warning">Super Admin Mode</span>
                @endif
            </div>
            <div class="card-body">
                <form action="{{ route('admin.payroll.monthly-expenses.store') }}" method="POST" id="expenseForm">
                    @csrf

                    @if(Auth::user()->role === 'super-admin')
                        <div class="mb-4">
                            <label for="company_uid" class="form-label">Company <span class="text-danger">*</span></label>
                            <select class="form-select @error('company_uid') is-invalid @enderror" name="company_uid" required>
                                <option value="">Select Company</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->company_uid }}" {{ old('company_uid') == $company->company_uid ? 'selected' : '' }}>
                                        {{ $company->company_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_uid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        <input type="hidden" name="company_uid" value="{{ $companies->first()->company_uid }}">
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-purchase-tag"></i></span>
                                <input type="text" class="form-control @error('purpose') is-invalid @enderror"
                                       name="purpose" required placeholder="Enter expense purpose"
                                       value="{{ old('purpose') }}">
                            </div>
                            @error('purpose')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="pay_to" class="form-label">Pay To <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" class="form-control @error('pay_to') is-invalid @enderror"
                                       name="pay_to" required placeholder="Enter payee name"
                                       value="{{ old('pay_to') }}">
                            </div>
                            @error('pay_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                <input type="date" class="form-control @error('date') is-invalid @enderror"
                                       name="date" required value="{{ old('date', date('Y-m-d')) }}">
                            </div>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-dollar"></i></span>
                                <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror"
                                       name="amount" required placeholder="Enter expense amount"
                                       value="{{ old('amount') }}">
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">Description</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-comment"></i></span>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                    name="description" rows="3" placeholder="Enter expense description">{{ old('description') }}</textarea>
                        </div>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.payroll.monthly-expenses') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-x me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-check me-1"></i> Create Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('expenseForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Saving...';
        });
    </script>
    @endpush
</x-app-layout>
