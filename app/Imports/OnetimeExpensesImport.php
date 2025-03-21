<?php

namespace App\Imports;

use App\Models\OnetimeExpens;
use App\Traits\PreviewImport;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Throwable;

class OnetimeExpensesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure, WithBatchInserts, WithStartRow, SkipsEmptyRows
{
    use PreviewImport;

    private $company_uid;
    private $is_super_admin;
    private $is_preview = false;
    private $processedCount = 0;
    private $importedRows = [];
    private $user;

    public function __construct($company_uid = null, $is_preview = false)
    {
        $this->user = Auth::user();
        $this->is_super_admin = $this->user->role === 'super-admin';

        // For regular users, always use their own company_uid regardless of what was passed
        if (!$this->is_super_admin) {
            $this->company_uid = $this->user->company_uid;
        } else {
            // For superadmins, use the provided company_uid or default to their own
            $this->company_uid = $company_uid ?? $this->user->company_uid;
        }

        $this->is_preview = $is_preview;
        $this->setPreviewMode($is_preview);
        $this->processedCount = 0;
        $this->importedRows = [];

        \Log::info('OnetimeExpensesImport initialized:', [
            'user_id' => $this->user->id,
            'user_email' => $this->user->email,
            'is_super_admin' => $this->is_super_admin,
            'company_uid' => $this->company_uid,
            'requested_company_uid' => $company_uid,
            'is_preview' => $this->is_preview
        ]);
    }

    public function startRow(): int
    {
        return 2; // Start from row 2 since row 1 is headers
    }

    public function headingRow(): int
    {
        return 1;
    }

    /**
     * Check if the row is empty
     */
    public function isEmptyRow(array $row): bool
    {
        return empty(array_filter($row, function ($value) {
            return $value !== null && $value !== '';
        }));
    }

    /**
     * Generate a unique key for the row
     */
    private function generateRowKey(array $row): string
    {
        return md5(json_encode([
            'purpose' => trim($row['purpose'] ?? ''),
            'pay_to' => trim($row['pay_to'] ?? ''),
            'amount' => (float)($row['amount'] ?? 0),
            'date' => $row['date'] ?? '',
        ]));
    }

    public function model(array $row)
    {
        try {
            // Skip empty rows or rows without required fields
            if ($this->isEmptyRow($row) ||
                empty($row['purpose'] ?? '') ||
                empty($row['pay_to'] ?? '') ||
                empty($row['amount'] ?? '') ||
                empty($row['date'] ?? '')) {
                return null;
            }

            // Check for duplicates in the current import
            $rowKey = $this->generateRowKey($row);
            if (isset($this->importedRows[$rowKey])) {
                \Log::warning('Skipping duplicate row in import:', [
                    'row' => $row,
                    'key' => $rowKey
                ]);
                return null;
            }

            // Mark this row as processed
            $this->importedRows[$rowKey] = true;

            // Debug log the raw row data
            \Log::info('Processing Excel row #' . (++$this->processedCount), [
                'raw_data' => $row,
                'row_keys' => array_keys($row),
                'row_key' => $rowKey
            ]);

            // Clean amount value
            $amount = $row['amount'] ?? null;
            if ($amount !== null) {
                $amount = str_replace([',', '$', '€', '£', ' '], '', $amount);
                if (!is_numeric($amount)) {
                    throw new \Exception("Invalid amount format");
                }
                $amount = (float) $amount;
            }

            // Convert date
            $date = null;
            if (isset($row['date'])) {
                if (is_numeric($row['date'])) {
                    $date = Date::excelToDateTimeObject($row['date'])->format('Y-m-d');
                } else {
                    $date = Carbon::parse($row['date'])->format('Y-m-d');
                }
            }

            // Handle company_uid - only superadmin can import for other companies
            $rowCompanyUid = $row['company_uid'] ?? null;
            $finalCompanyUid = $this->company_uid;

            if ($this->is_super_admin && !empty($rowCompanyUid)) {
                $finalCompanyUid = $rowCompanyUid;
                \Log::info('Superadmin importing for different company:', [
                    'row_company_uid' => $rowCompanyUid,
                    'user_company_uid' => $this->user->company_uid
                ]);
            } else if (!$this->is_super_admin && !empty($rowCompanyUid) && $rowCompanyUid !== $this->company_uid) {
                // Log attempt by regular user to import for different company
                \Log::warning('Regular user attempted to import for different company:', [
                    'user_id' => $this->user->id,
                    'user_company_uid' => $this->company_uid,
                    'row_company_uid' => $rowCompanyUid
                ]);
            }

            // Prepare row data
            $rowData = [
                'company_uid' => $finalCompanyUid,
                'purpose' => trim($row['purpose'] ?? ''),
                'pay_to' => trim($row['pay_to'] ?? ''),
                'amount' => $amount,
                'date' => $date,
                'description' => trim($row['description'] ?? ''),
                'payment_status' => trim(strtolower($row['payment_status'] ?? 'pending')),
                '_excel_row' => $this->processedCount, // Add row number for tracking
                '_row_key' => $rowKey, // Add row key for tracking
            ];

            // Skip if any required field is empty after processing
            if (empty($rowData['purpose']) || empty($rowData['pay_to']) ||
                empty($rowData['amount']) || empty($rowData['date'])) {
                return null;
            }

            // Validate row
            $errors = $this->validateRow($rowData);
            $isValid = empty($errors);

            // Add to preview if in preview mode
            if ($this->is_preview) {
                $this->addToPreview($rowData, $isValid, $errors);
                return null;
            }

            // Return null if row is invalid
            if (!$isValid) {
                return null;
            }

            // Remove tracking fields before creating model
            unset($rowData['_excel_row']);
            unset($rowData['_row_key']);

            // Create and return the model
            return new OnetimeExpens($rowData);

        } catch (\Exception $e) {
            \Log::error('Import row error: ' . $e->getMessage(), [
                'exception' => $e,
                'row' => $row ?? [],
                'processed_count' => $this->processedCount
            ]);

            if ($this->is_preview) {
                $this->addToPreview($rowData ?? [
                    'error' => $e->getMessage(),
                    '_excel_row' => $this->processedCount
                ], false, [$e->getMessage()]);
            }

            return null;
        }
    }

    public function rules(): array
    {
        return [
            '*.purpose' => 'required|string|max:255',
            '*.pay_to' => 'required|string|max:255',
            '*.amount' => 'required|numeric|min:0',
            '*.date' => 'required|date',
            '*.description' => 'nullable|string',
            '*.payment_status' => 'nullable|string|in:pending,paid,cancelled',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.purpose.required' => 'The purpose field is required.',
            '*.pay_to.required' => 'The pay to field is required.',
            '*.amount.required' => 'The amount field is required.',
            '*.amount.numeric' => 'The amount must be a number.',
            '*.date.required' => 'The date field is required.',
            '*.date.date' => 'The date must be a valid date.',
            '*.payment_status.in' => 'The payment status must be one of: pending, paid, cancelled.',
        ];
    }

    public function onError(Throwable $e)
    {
        \Log::error('Import error: ' . $e->getMessage());
        $this->errors[] = $e->getMessage();
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $row = $failure->row();
            $errors = $failure->errors();
            $this->errors[] = "Row {$row}: " . implode(', ', $errors);
        }
    }

    public function batchSize(): int
    {
        return 100;
    }
}
