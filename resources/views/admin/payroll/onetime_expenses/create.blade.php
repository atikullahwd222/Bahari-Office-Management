<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create One-time Expense') }}
            </h2>
            <a href="{{ route('admin.payroll.onetime-expenses') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to List
            </a>
        </div>
    </x-slot>

    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <div class="card">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="card-title mb-0 text-white">
                    <i class="bx bx-money me-2"></i> Create New One-time Expense
                </h5>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.payroll.onetime-expenses.store') }}" method="POST">
                    @csrf

                    @if(Auth::user()->role === 'super-admin')
                        <div class="mb-3">
                            <label for="company_uid" class="form-label">Company</label>
                            <select name="company_uid" id="company_uid" class="form-select @error('company_uid') is-invalid @enderror" required>
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
                        <input type="hidden" name="company_uid" value="{{ Auth::user()->company_uid }}">
                    @endif

                    <div class="mb-3">
                        <label for="purpose" class="form-label">Purpose</label>
                        <input type="text" class="form-control @error('purpose') is-invalid @enderror"
                               id="purpose" name="purpose" value="{{ old('purpose') }}" required>
                        @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="pay_to" class="form-label">Pay To</label>
                        <input type="text" class="form-control @error('pay_to') is-invalid @enderror"
                               id="pay_to" name="pay_to" value="{{ old('pay_to') }}" required>
                        @error('pay_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-check-circle"></i></span>
                                <select class="form-select @error('payment_status') is-invalid @enderror" name="payment_status">
                                    @foreach(App\Models\OnetimeExpens::getPaymentStatuses() as $value => $label)
                                        <option value="{{ $value }}" {{ old('payment_status', 'pending') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('payment_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-4">
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
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.payroll.onetime-expenses') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .form-label {
            font-weight: 500;
        }
        .invalid-feedback {
            display: block;
        }
    </style>
    @endpush

    @if(session('verify') === 'expense-updated')
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
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
            });
        </script>
        @endpush
    @endif
</x-app-layout>
