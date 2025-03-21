<?php

namespace App\Traits;

trait PreviewImport
{
    protected $isPreview = false;
    protected $previewData = [];
    protected $errors = [];
    protected $processedRows = [];
    protected $rowNumber = 1;
    protected $processedHashes = []; // Property to track unique rows

    public function setPreviewMode($isPreview = true)
    {
        $this->isPreview = $isPreview;
        $this->previewData = [];
        $this->errors = [];
        $this->processedRows = [];
        $this->processedHashes = []; // Reset hashes
        $this->rowNumber = 1;
        return $this;
    }

    public function getPreviewData()
    {
        // Remove any null entries that might have slipped through
        $this->previewData = array_filter($this->previewData);

        // Reset array keys for consistent JSON output
        $this->previewData = array_values($this->previewData);

        // Log preview data for debugging
        \Log::info('Preview Data Summary:', [
            'total_rows' => count($this->previewData),
            'valid_rows' => count(array_filter($this->previewData, fn($row) => $row['is_valid'])),
            'error_rows' => count(array_filter($this->previewData, fn($row) => !$row['is_valid'])),
            'duplicate_rows' => count(array_filter($this->previewData, fn($row) => $row['data']['is_duplicate'] ?? false)),
            'processed_hashes_count' => count($this->processedHashes ?? [])
        ]);

        return [
            'total_rows' => count($this->previewData),
            'valid_rows' => count(array_filter($this->previewData, fn($row) => $row['is_valid'])),
            'error_rows' => count(array_filter($this->previewData, fn($row) => !$row['is_valid'])),
            'duplicate_rows' => count(array_filter($this->previewData, fn($row) => $row['data']['is_duplicate'] ?? false)),
            'data' => $this->previewData,
            'errors' => $this->errors
        ];
    }

    protected function addToPreview($row, $isValid = true, $errors = [])
    {
        if ($this->isPreview) {
            // Skip if row is empty or doesn't have required fields
            if (empty($row) ||
                empty($row['purpose'] ?? '') ||
                empty($row['pay_to'] ?? '') ||
                empty($row['amount'] ?? '') ||
                empty($row['date'] ?? '')) {
                \Log::warning('Skipping invalid row in preview:', [
                    'row_data' => $row
                ]);
                return;
            }

            // Use the row key if available, otherwise generate one
            $rowHash = $row['_row_key'] ?? md5(json_encode([
                'purpose' => $row['purpose'] ?? '',
                'pay_to' => $row['pay_to'] ?? '',
                'amount' => $row['amount'] ?? '',
                'date' => $row['date'] ?? '',
                'description' => $row['description'] ?? ''
            ]));

            // Skip if we've already processed this exact row
            if (isset($this->processedHashes[$rowHash])) {
                \Log::warning('Skipping duplicate row in preview:', [
                    'row_data' => $row,
                    'hash' => $rowHash
                ]);
                return;
            }

            // Mark this row as processed
            $this->processedHashes[$rowHash] = true;

            // Log the row being added to preview
            \Log::info('Adding row to preview:', [
                'row_number' => $this->rowNumber,
                'is_valid' => $isValid,
                'has_errors' => !empty($errors),
                'is_duplicate' => $row['is_duplicate'] ?? false,
                'excel_row' => $row['_excel_row'] ?? 'unknown',
                'hash' => $rowHash
            ]);

            $previewRow = [
                'row_number' => $this->rowNumber++,
                'is_valid' => $isValid,
                'data' => $row,
                'errors' => $errors
            ];

            $this->previewData[] = $previewRow;

            if (!$isValid && !empty($errors)) {
                $this->errors[] = [
                    'row' => $this->rowNumber - 1,
                    'errors' => $errors
                ];
            }
        }
    }

    protected function validateRow($row)
    {
        $errors = [];

        // Basic required field validation
        $requiredFields = ['purpose', 'pay_to', 'amount', 'date'];
        foreach ($requiredFields as $field) {
            if (empty($row[$field])) {
                $errors[] = "The {$field} field is required.";
            }
        }

        // Amount validation
        if (isset($row['amount'])) {
            if (!is_numeric($row['amount'])) {
                $errors[] = "The amount must be a number.";
            } elseif ($row['amount'] < 0) {
                $errors[] = "The amount must be a positive number.";
            }
        }

        // Date validation
        if (isset($row['date'])) {
            try {
                $date = new \DateTime($row['date']);
            } catch (\Exception $e) {
                $errors[] = "The date is not in a valid format.";
            }
        }

        // Payment status validation if provided
        if (!empty($row['payment_status'])) {
            $validStatuses = ['pending', 'paid', 'cancelled'];
            if (!in_array(strtolower($row['payment_status']), $validStatuses)) {
                $errors[] = "Invalid payment status. Must be one of: " . implode(', ', $validStatuses);
            }
        }

        return $errors;
    }

    protected function isValidDate($date)
    {
        // Check if it's a numeric Excel date
        if (is_numeric($date)) {
            return true;
        }

        // Check if it's a valid date string
        try {
            $dateObj = \Carbon\Carbon::parse($date);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function isValidCompanyUid($companyUid)
    {
        return \App\Models\CompanySetting::where('company_uid', $companyUid)->exists();
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
