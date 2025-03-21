<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnetimeExpens extends Model
{
    use HasFactory;

    protected $table = 'onetime_expenses';

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'company_uid',
        'purpose',
        'pay_to',
        'amount',
        'date',
        'description',
        'payment_status',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public static function getPaymentStatuses()
    {
        return [
            self::PAYMENT_STATUS_PENDING => 'Pending',
            self::PAYMENT_STATUS_PAID => 'Paid',
            self::PAYMENT_STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public function company()
    {
        return $this->belongsTo(CompanySetting::class, 'company_uid', 'company_uid');
    }

    public function payroll()
    {
        return $this->hasOne(Payroll::class, 'reference_id', 'id')->where('type', 'onetime-expense');
    }
}
