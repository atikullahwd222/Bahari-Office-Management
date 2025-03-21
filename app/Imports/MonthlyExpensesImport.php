<?php

namespace App\Imports;

use App\Models\MonthlyExpens;
use App\Traits\PreviewImport;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Throwable;

class MonthlyExpensesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure, WithBatchInserts, WithStartRow
{
    use PreviewImport;

    private $company_uid;
    private $is_super_admin;
    private $is_preview = false;
    private $importedRows = [];

    public function __construct($company_uid = null, $is_preview = false)
    {
        $this->is_super_admin = Auth::user()->role === 'super-admin';
        $this->company_uid = $company_uid ?? Auth::user()->company_uid;
        $this->is_preview = $is_preview;
        $this->setPreviewMode($is_preview);
    }

    public function startRow(): int
    {
        return 2; // Start from row 2 since row 1 is headers
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function model(array $row)
    {
        try {
            // Skip empty rows
            if (empty(array_filter($row))) {
                return null;
            }

            // Debug log the row data
            \Log::info('Processing row:', $row);

            // Clean amount value - handle various formats
            $amount = $row['amount'] ?? null;
            if ($amount !== null) {
                // Remove any currency symbols and thousand separators
                $amount = str_replace([',', '$', '€', '£', ' '], '', $amount);
                // Convert decimal separator if needed
                if (strpos($amount, ',') !== false) {
                    $amount = str_replace(',', '.', $amount);
                }
                // Ensure it's a valid number
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

            // Prepare row data
            $rowData = [
                'company_uid' => $this->company_uid, // Always use the company_uid from constructor
                'purpose' => trim($row['purpose'] ?? ''),
                'pay_to' => trim($row['pay_to'] ?? ''),
                'amount' => $amount,
                'date' => $date,
                'description' => trim($row['description'] ?? '')
            ];

            // Validate row
            $errors = $this->validateRow($rowData);
            $isValid = empty($errors);

            // Add to preview if in preview mode
            $this->addToPreview($rowData, $isValid, $errors);

            // Return null if in preview mode or row is invalid
            if ($this->is_preview || !$isValid) {
                return null;
            }

            // Check for duplicates during import
            $uniqueFields = [
                'purpose' => $rowData['purpose'],
                'pay_to' => $rowData['pay_to'],
                'amount' => $rowData['amount'],
                'description' => $rowData['description']
            ];

            $rowKey = md5(serialize($uniqueFields));

            // Skip if we've already imported this row
            if (isset($this->importedRows[$rowKey])) {
                return null;
            }

            // Mark this row as imported
            $this->importedRows[$rowKey] = true;

            // Create and return the model
            return new MonthlyExpens($rowData);

        } catch (\Exception $e) {
            \Log::error('Import row error: ' . $e->getMessage());
            $this->errors[] = "Row error: " . $e->getMessage();
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
            \Log::warning("Import failure at row {$row}:", $errors);
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function batchSize(): int
    {
        return 100;
    }
}
