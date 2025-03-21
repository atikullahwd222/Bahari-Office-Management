<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Monthly Expenses') }}
            </h2>
            <a href="{{ route('admin.payroll.monthly-expenses.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add New Expense
            </a>
        </div>
    </x-slot>

    @if (session('verify') === 'expense-updated')
        <div class="alert alert-{{ session('status') }} alert-dismissible text-dark" role="alert">
            <b>{{ session('message') }}</b>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <div class="card">
            <div class="card-header bg-primary text-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-white">
                        <i class="bx bx-money me-2"></i> Monthly Expenses
                    </h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="bx bx-down-arrow-alt me-1"></i> Import
                        </button>
                        <a href="{{ route('admin.payroll.monthly-expenses.create') }}" class="btn btn-sm btn-outline-light">
                            <i class="bx bx-plus me-1"></i> Add New
                        </a>
                    </div>
                </div>
            </div>


            <div class="card-body">
                <div class="table-responsive mt-3">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <style>
                                .table-dark th {
                                    color: #fff !important;
                                }
                            </style>
                            <tr>
                                @if(Auth::user()->role === 'super-admin')
                                    <th>Company</th>
                                @endif
                                <th>Purpose</th>
                                <th>Pay To</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr>
                                    @if(Auth::user()->role === 'super-admin')
                                        <td>{{ $expense->company->company_name }}</td>
                                    @endif
                                    <td>{{ $expense->purpose }}</td>
                                    <td>{{ $expense->pay_to }}</td>
                                    <td>{{ number_format($expense->amount, 2) }}</td>
                                    <td>{{ date('F j, Y', strtotime($expense->date)) }}</td>
                                    <td>{{ $expense->description ?? 'N/A' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-danger delete-expense"
                                                    data-expense-id="{{ $expense->id }}"
                                                    data-expense-purpose="{{ $expense->purpose }}">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                            <a href="{{ route('admin.payroll.monthly-expenses.edit', $expense->id) }}" class="btn btn-sm btn-primary">
                                                <i class="bx bx-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->role === 'super-admin' ? 7 : 6 }}" class="text-center">
                                        No expenses found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $expenses->firstItem() ?? 0 }} to {{ $expenses->lastItem() ?? 0 }} of {{ $expenses->total() }} entries
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            {{-- Previous Page Link --}}
                            @if ($expenses->onFirstPage())
                                <li class="page-item prev disabled">
                                    <a class="page-link" href="javascript:void(0);">
                                        <i class="tf-icon bx bx-chevrons-left"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item prev">
                                    <a class="page-link" href="{{ $expenses->previousPageUrl() }}">
                                        <i class="tf-icon bx bx-chevrons-left"></i>
                                    </a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($expenses->getUrlRange(max(1, $expenses->currentPage() - 2), min($expenses->lastPage(), $expenses->currentPage() + 2)) as $page => $url)
                                @if ($page == $expenses->currentPage())
                                    <li class="page-item active">
                                        <a class="page-link" href="javascript:void(0);">{{ $page }}</a>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($expenses->hasMorePages())
                                <li class="page-item next">
                                    <a class="page-link" href="{{ $expenses->nextPageUrl() }}">
                                        <i class="tf-icon bx bx-chevrons-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item next disabled">
                                    <a class="page-link" href="javascript:void(0);">
                                        <i class="tf-icon bx bx-chevrons-right"></i>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Monthly Expenses</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="importForm" action="{{ route('admin.payroll.monthly-expenses.import') }}" method="POST" enctype="multipart/form-data">
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
                                <li>purpose (required)</li>
                                <li>pay_to (required)</li>
                                <li>amount (required - numeric value)</li>
                                <li>date (required - Excel date format or YYYY-MM-DD)</li>
                                <li>description (optional)</li>
                            </ul>
                            <p class="mt-2 mb-0"><small>Notes:</small></p>
                            <ul class="mb-0">
                                <li><small>Date can be in Excel date format or text format (YYYY-MM-DD)</small></li>
                                <li><small>Amount can include currency symbols and thousand separators (e.g., $2,500.00)</small></li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-info" id="previewBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Preview Data
                        </button>
                        <button type="submit" class="btn btn-primary" id="importBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Import Expenses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">Preview Import Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <div class="d-flex gap-3">
                            <div>
                                <strong>Total Rows:</strong>
                                <span id="totalRows">0</span>
                            </div>
                            <div>
                                <strong>Valid Rows:</strong>
                                <span id="validRows">0</span>
                            </div>
                            <div>
                                <strong>Error Rows:</strong>
                                <span id="errorRows">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Row</th>
                                    <th>Purpose</th>
                                    <th>Pay To</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Errors</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody">
                                <tr>
                                    <td colspan="8" class="text-center">No data to preview</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="confirmImportBtn">Confirm Import</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Add the showDeleteConfirmation function
        function showDeleteConfirmation(options) {
            return Swal.fire({
                title: options.title,
                text: options.text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Handle delete button clicks
            document.querySelectorAll('.delete-expense').forEach(button => {
                button.addEventListener('click', function() {
                    const expenseId = this.dataset.expenseId;
                    const expensePurpose = this.dataset.expensePurpose;

                    showDeleteConfirmation({
                        title: 'Delete Expense',
                        text: `Are you sure you want to delete the expense "${expensePurpose}"?`
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Create a form to submit DELETE request
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '{{ route('admin.payroll.monthly-expenses.delete', ':id') }}'.replace(':id', expenseId);

                            // Add CSRF token
                            const csrfToken = document.createElement('input');
                            csrfToken.type = 'hidden';
                            csrfToken.name = '_token';
                            csrfToken.value = '{{ csrf_token() }}';
                            form.appendChild(csrfToken);

                            // Add method override for DELETE
                            const methodField = document.createElement('input');
                            methodField.type = 'hidden';
                            methodField.name = '_method';
                            methodField.value = 'DELETE';
                            form.appendChild(methodField);

                            // Add form to document and submit
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });

            // Import and Preview functionality
            const importForm = document.getElementById('importForm');
            const previewBtn = document.getElementById('previewBtn');
            const importBtn = document.getElementById('importBtn');
            const confirmImportBtn = document.getElementById('confirmImportBtn');
            const importModal = new bootstrap.Modal(document.getElementById('importModal'));
            const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));

            // Preview button click handler
            if (previewBtn) {
                previewBtn.addEventListener('click', async function() {
                    const formData = new FormData(importForm);
                    const spinner = this.querySelector('.spinner-border');

                    try {
                        // Show loading state
                        this.disabled = true;
                        spinner.classList.remove('d-none');

                        // Send preview request
                        const response = await fetch("{{ route('admin.payroll.monthly-expenses.preview') }}", {
                            method: "POST",
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Error previewing data');
                        }

                        // Update preview modal with data
                        updatePreviewModal(data.preview);

                        // Hide import modal and show preview modal
                        importModal.hide();
                        previewModal.show();

                    } catch (error) {
                        console.error('Preview error:', error);
                        showErrorAlert(error.message || 'Error previewing data');
                    } finally {
                        // Reset loading state
                        this.disabled = false;
                        spinner.classList.add('d-none');
                    }
                });
            }

            // Confirm import button click handler
            confirmImportBtn.addEventListener('click', async function() {
                const formData = new FormData(importForm);
                const spinner = importBtn.querySelector('.spinner-border');

                try {
                    // Show loading state
                    this.disabled = true;
                    spinner.classList.remove('d-none');

                    // Send import request
                    const response = await fetch(importForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Error importing data');
                    }

                    // Show success message and reload page
                    showSuccessAlert('Data imported successfully');
                    setTimeout(() => window.location.reload(), 1500);

                } catch (error) {
                    console.error('Import error:', error);
                    showErrorAlert(error.message || 'Error importing data');
                } finally {
                    // Reset loading state
                    this.disabled = false;
                    spinner.classList.add('d-none');
                }
            });

            // Function to update preview modal with data
            function updatePreviewModal(preview) {
                const tableBody = document.getElementById('previewTableBody');
                const totalRows = document.getElementById('totalRows');
                const validRows = document.getElementById('validRows');
                const errorRows = document.getElementById('errorRows');

                // Set summary information
                totalRows.textContent = preview.total_rows || 0;
                validRows.textContent = preview.valid_rows || 0;
                errorRows.textContent = preview.error_rows || 0;

                // Clear existing rows
                tableBody.innerHTML = '';

                // Get preview data array
                const previewData = preview.data || [];

                if (!previewData.length) {
                    tableBody.innerHTML = '<tr><td colspan="8" class="text-center">No data to preview</td></tr>';
                    return;
                }

                // Add preview rows
                previewData.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.className = row.is_valid ? '' : 'table-danger';

                    // Get row data and errors
                    const data = row.data || {};
                    const errors = Array.isArray(row.errors) ? row.errors.join(', ') : '';

                    // Format amount if it exists
                    const amount = data.amount ? new Intl.NumberFormat('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(data.amount) : '';

                    // Format date if it exists
                    const date = data.date ? new Date(data.date).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    }) : '';

                    tr.innerHTML = `
                        <td>${row.row_number}</td>
                        <td>${escapeHtml(data.purpose || '')}</td>
                        <td>${escapeHtml(data.pay_to || '')}</td>
                        <td>${escapeHtml(amount)}</td>
                        <td>${escapeHtml(date)}</td>
                        <td>${escapeHtml(data.description || '')}</td>
                        <td>
                            <span class="badge bg-${row.is_valid ? 'success' : 'danger'}">
                                ${row.is_valid ? 'Valid' : 'Error'}
                            </span>
                        </td>
                        <td>${escapeHtml(errors)}</td>
                    `;

                    tableBody.appendChild(tr);
                });
            }

            // Helper function to escape HTML and prevent XSS
            function escapeHtml(unsafe) {
                if (unsafe === null || unsafe === undefined) return '';
                return unsafe
                    .toString()
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            // Helper function to show error alert
            function showErrorAlert(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonText: 'OK'
                });
            }

            // Helper function to show success alert
            function showSuccessAlert(message) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: message,
                    confirmButtonText: 'OK'
                });
            }
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .pagination {
            margin-bottom: 0;
        }
        .page-link {
            padding: 0.625rem 0.75rem;
            min-width: 40px;
            text-align: center;
        }
        .page-item.active .page-link {
            background-color: #696cff;
            border-color: #696cff;
        }
        .page-item.disabled .page-link {
            color: #a5a6ab;
        }
        .page-link:hover {
            background-color: #e1e2ff;
            border-color: #e1e2ff;
            color: #696cff;
        }
        .page-link:focus {
            box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25);
        }
    </style>
    @endpush
</x-app-layout>
