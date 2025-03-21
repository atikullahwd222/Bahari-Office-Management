<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $table = 'payrolls';

    protected $fillable = [
        'company_uid',
        'type',
        'reference_id',
        'amount',
        'due_date',
        'status'
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2'
    ];

    /**
     * Get the company that owns the payroll record.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanySetting::class, 'company_uid', 'company_uid');
    }

    /**
     * Get the employee setup if this is a salary payment.
     */
    public function employeeSetup(): BelongsTo
    {
        return $this->belongsTo(EmployeeSetup::class, 'reference_id');
    }

    /**
     * Get the monthly expense if this is an expense payment.
     */
    public function monthlyExpense(): BelongsTo
    {
        return $this->belongsTo(MonthlyExpens::class, 'reference_id');
    }

    /**
     * Get the one-time expense if this is an expense payment.
     */
    public function onetimeExpense(): BelongsTo
    {
        return $this->belongsTo(OnetimeExpens::class, 'reference_id');
    }

    /**
     * Get the available payment statuses.
     */
    public static function getPaymentStatuses(): array
    {
        return [
            'pending'   => 'Pending',
            'paid'      => 'Paid',
        ];
    }
}
