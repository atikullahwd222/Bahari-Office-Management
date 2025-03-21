<?php

namespace App\Imports;

trait PreviewImport
{
    protected $preview_data = [];
    protected $preview_errors = [];
    protected $row_count = 0;

    public function preview(array $row)
    {
        $this->row_count++;

        // Skip empty rows
        if (empty(array_filter($row))) {
            return;
        }

        try {
            // Validate the row
            $validator = validator($row, $this->rules(), $this->customValidationMessages() ?? []);

            if ($validator->fails()) {
                $this->preview_errors[] = [
                    'row' => $this->row_count,
                    'errors' => $validator->errors()->all()
                ];
            }

            // Store preview data
            $this->preview_data[] = [
                'row_number' => $this->row_count,
                'data' => $row,
                'is_valid' => !$validator->fails()
            ];

        } catch (\Exception $e) {
            $this->preview_errors[] = [
                'row' => $this->row_count,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function getPreviewData()
    {
        return [
            'data' => $this->preview_data,
            'errors' => $this->preview_errors,
            'total_rows' => $this->row_count,
            'valid_rows' => count(array_filter($this->preview_data, fn($row) => $row['is_valid'])),
            'error_rows' => count($this->preview_errors)
        ];
    }
}
