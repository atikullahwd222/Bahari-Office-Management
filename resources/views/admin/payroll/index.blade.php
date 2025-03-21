<x-app-layout>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
            <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Monthly Expenses</span>
                <h3 class="card-title mb-2">৳ {{ $totalMonthlyExpense }}</h3>
                <small class="text-primary fw-semibold"><i class="bx bx-up-arrow-alt"></i> Previous Month</small>
            </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
            <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Onetime Expenses</span>
                <h3 class="card-title mb-2">৳ {{ $totalOnetimeExpense }}</h3>
                <small class="text-primary fw-semibold"><i class="bx bx-up-arrow-alt"></i> Previous Month</small>
            </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
            <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Selary Expenses</span>
                <h3 class="card-title mb-2">৳ {{ $totalSalary }}</h3>
                <small class="text-primary fw-semibold"><i class="bx bx-up-arrow-alt"></i> Previous Month</small>
            </div>
            </div>
        </div>

        @if(auth()->user()->is_superadmin)
        <!-- Payroll Settings Section -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Payroll Settings</h5>
                    <button type="button" class="btn btn-primary" onclick="saveSettings()">
                        <i class="bx bx-save"></i> Save Settings
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Days to Show Upcoming Payroll</label>
                            <input type="number" class="form-control" id="days_to_show"
                                   value="{{ $settings['days_to_show'] }}" min="1" max="60">
                            <small class="text-muted">Number of days to show in upcoming payroll (1-60 days)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Notification Days</label>
                            <input type="number" class="form-control" id="notification_days"
                                   value="{{ $settings['notification_days'] }}" min="1" max="10">
                            <small class="text-muted">Days before due date to start notifications (1-10 days)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency</label>
                            <input type="text" class="form-control" id="currency"
                                   value="{{ $settings['currency'] }}" maxlength="10">
                            <small class="text-muted">Currency code (e.g., BDT, USD)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Auto Generate Payroll</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="auto_generate"
                                       {{ $settings['auto_generate'] ? 'checked' : '' }}>
                                <label class="form-check-label">Enable automatic payroll generation</label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Weekend Days</label>
                            <div class="d-flex flex-wrap gap-3">
                                @php
                                    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                @endphp
                                @foreach($days as $index => $day)
                                    <div class="form-check">
                                        <input class="form-check-input weekend-day" type="checkbox"
                                               value="{{ $index }}" id="day_{{ $index }}"
                                               {{ in_array($index, $settings['weekend_days']) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="day_{{ $index }}">
                                            {{ $day }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">
                                Last updated: {{ \Carbon\Carbon::parse($settings['updated_at'])->format('M d, Y H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Pending Payroll Section -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Pending Payroll
                        <span class="badge bg-warning ms-1">{{ $pendingPayroll->count() }}</span>
                    </h5>
                    <div>
                        <button type="button" class="btn btn-success me-2" id="bulkMarkAsPaidBtn" style="display: none;" onclick="bulkMarkAsPaid()">
                            <i class="bx bx-check"></i> Mark Selected as Paid
                        </button>
                        <button type="button" class="btn btn-primary" onclick="generatePayroll()">
                            <i class="bx bx-plus"></i> Generate Payroll
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAllPayrolls" onchange="toggleSelectAll()">
                                        </div>
                                    </th>
                                    <th>Type</th>
                                    <th>Company</th>
                                    <th>Details</th>
                                    <th>Amount</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingPayroll as $payroll)
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input payroll-checkbox" type="checkbox" value="{{ $payroll->id }}" onchange="updateBulkButton()">
                                            </div>
                                        </td>
                                        <td>
                                            @if($payroll->type === 'salary')
                                                <span class="badge bg-success">Salary</span>
                                            @elseif($payroll->type === 'expense')
                                                <span class="badge bg-info">Expense</span>
                                            @elseif($payroll->type === 'onetime-expense')
                                                <span class="badge bg-warning">Onetime</span>
                                            @endif
                                        </td>
                                        <td>{{ $payroll->company->company_name ?? 'N/A' }}</td>
                                        <td>
                                            @if($payroll->type === 'salary')
                                                @if($payroll->employeeSetup && $payroll->employeeSetup->employee)
                                                    {{ $payroll->employeeSetup->employee->first_name }}
                                                    {{ $payroll->employeeSetup->employee->last_name }}
                                                @else
                                                    N/A
                                                @endif
                                            @elseif($payroll->type === 'expense')
                                                @if($payroll->monthlyExpense)
                                                    {{ $payroll->monthlyExpense->purpose }}
                                                @elseif($payroll->onetimeExpense)
                                                    {{ $payroll->onetimeExpense->purpose }}
                                                    <span class="badge bg-warning">One-time</span>
                                                @else
                                                    {{ $payroll->reference_id }} (Reference ID)
                                                @endif
                                            @endif
                                        </td>
                                        <td>{{ number_format($payroll->amount, 2) }} BDT</td>
                                        <td>{{ $payroll->due_date->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-warning">Pending</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success"
                                                    onclick="markAsPaid({{ $payroll->id }})">
                                                <i class="bx bx-check"></i> Mark as Paid
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No pending payroll entries found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paid Payroll Section -->
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Paid Payroll
                        <span class="badge bg-success ms-1">{{ $paidPayroll->count() }}</span>
                    </h5>
                    <div class="d-flex align-items-center">
                        <label class="me-2">Select Month:</label>
                        <select class="form-select" onchange="changeMonth(this.value)">
                            @foreach($months as $month)
                                <option value="{{ $month['value'] }}"
                                        {{ $selectedMonth === $month['value'] ? 'selected' : '' }}>
                                    {{ $month['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Company</th>
                                    <th>Details</th>
                                    <th>Amount</th>
                                    <th>Due Date</th>
                                    <th>Paid Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paidPayroll as $payroll)
                                    <tr>
                                        <td>
                                            @if($payroll->type === 'salary')
                                                <span class="badge bg-success">Salary</span>
                                            @elseif($payroll->type === 'expense')
                                                <span class="badge bg-info">Expense</span>
                                            @endif
                                        </td>
                                        <td>{{ $payroll->company->company_name ?? 'N/A' }}</td>
                                        <td>
                                            @if($payroll->type === 'salary')
                                                @if($payroll->employeeSetup && $payroll->employeeSetup->employee)
                                                    {{ $payroll->employeeSetup->employee->first_name }}
                                                    {{ $payroll->employeeSetup->employee->last_name }}
                                                @else
                                                    N/A
                                                @endif
                                            @elseif($payroll->type === 'expense')
                                                @if($payroll->monthlyExpense)
                                                    {{ $payroll->monthlyExpense->purpose }}
                                                @elseif($payroll->onetimeExpense)
                                                    {{ $payroll->onetimeExpense->purpose }}
                                                    <span class="badge bg-warning">One-time</span>
                                                @else
                                                    {{ $payroll->reference_id }} (Reference ID)
                                                @endif
                                            @endif
                                        </td>
                                        <td>{{ number_format($payroll->amount, 2) }} BDT</td>
                                        <td>{{ $payroll->due_date->format('M d, Y') }}</td>
                                        <td>{{ $payroll->updated_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No paid payroll entries found for selected month</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function generatePayroll() {
    $.ajax({
        url: '{{ route("admin.payroll.generate") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.status === 'success' || response.status === 'info') {
                Swal.fire({
                    icon: response.status,
                    title: 'Success',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to generate payroll'
            });
        }
    });
}

function markAsPaid(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the payment as paid",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, mark as paid'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/payroll/${id}/mark-as-paid`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Payment marked as paid',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to mark payment as paid'
                    });
                }
            });
        }
    });
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAllPayrolls');
    const checkboxes = document.querySelectorAll('.payroll-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });

    updateBulkButton();
}

function updateBulkButton() {
    const checkboxes = document.querySelectorAll('.payroll-checkbox:checked');
    const bulkButton = document.getElementById('bulkMarkAsPaidBtn');

    if (checkboxes.length > 0) {
        bulkButton.style.display = 'inline-block';
        bulkButton.innerText = `Mark Selected (${checkboxes.length}) as Paid`;
    } else {
        bulkButton.style.display = 'none';
    }
}

function bulkMarkAsPaid() {
    const checkboxes = document.querySelectorAll('.payroll-checkbox:checked');
    const payrollIds = Array.from(checkboxes).map(checkbox => checkbox.value);

    if (payrollIds.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Entries Selected',
            text: 'Please select at least one payroll entry to mark as paid'
        });
        return;
    }

    Swal.fire({
        title: 'Are you sure?',
        text: `This will mark ${payrollIds.length} payment(s) as paid`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, mark as paid'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.payroll.bulk-mark-as-paid") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    payroll_ids: payrollIds
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to mark payments as paid';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                }
            });
        }
    });
}

function changeMonth(month) {
    window.location.href = `{{ route('admin.payroll.index') }}?month=${month}`;
}

function saveSettings() {
    // Get weekend days
    const weekendDays = [];
    document.querySelectorAll('.weekend-day:checked').forEach(checkbox => {
        weekendDays.push(parseInt(checkbox.value));
    });

    const settings = {
        days_to_show: parseInt(document.getElementById('days_to_show').value),
        notification_days: parseInt(document.getElementById('notification_days').value),
        currency: document.getElementById('currency').value,
        auto_generate: document.getElementById('auto_generate').checked,
        weekend_days: weekendDays,
        _token: '{{ csrf_token() }}'
    };

    $.ajax({
        url: '{{ route("admin.payroll.settings.update") }}',
        type: 'POST',
        data: settings,
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        },
        error: function(xhr) {
            let errorMessage = 'Failed to update settings';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage
            });
        }
    });
}
</script>
@endpush
</x-app-layout>
