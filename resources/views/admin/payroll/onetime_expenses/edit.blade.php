<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit One-time Expense') }}
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
                    <i class="bx bx-edit me-2"></i> Edit One-time Expense
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.payroll.onetime-expenses.update', $expense->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @if(session('verify') === 'expense-updated')
                        <div class="alert alert-{{ session('status') }} alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        @if(Auth::user()->role === 'super-admin')
                            <div class="col-md-6 mb-3">
                                <label for="company_uid" class="form-label">Company</label>
                                <select name="company_uid" id="company_uid" class="form-select @error('company_uid') is-invalid @enderror" required>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->company_uid }}" {{ $expense->company_uid == $company->company_uid ? 'selected' : '' }}>
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

                        <div class="col-md-6 mb-3">
                            <label for="purpose" class="form-label">Purpose</label>
                            <input type="text" class="form-control @error('purpose') is-invalid @enderror"
                                id="purpose" name="purpose" value="{{ old('purpose', $expense->purpose) }}" required>
                            @error('purpose')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="pay_to" class="form-label">Pay To</label>
                            <input type="text" class="form-control @error('pay_to') is-invalid @enderror"
                                id="pay_to" name="pay_to" value="{{ old('pay_to', $expense->pay_to) }}" required>
                            @error('pay_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror"
                                id="amount" name="amount" value="{{ old('amount', $expense->amount) }}" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror"
                                id="date" name="date" value="{{ old('date', $expense->date->format('Y-m-d')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select class="form-select @error('payment_status') is-invalid @enderror"
                                    id="payment_status" name="payment_status">
                                @foreach(App\Models\OnetimeExpens::getPaymentStatuses() as $value => $label)
                                    <option value="{{ $value }}" {{ old('payment_status', $expense->payment_status) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                id="description" name="description" rows="3">{{ old('description', $expense->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.payroll.onetime-expenses') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
