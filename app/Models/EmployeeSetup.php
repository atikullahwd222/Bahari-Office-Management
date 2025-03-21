<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Company;
use App\Models\Payroll;

class EmployeeSetup extends Model
{
    protected $table = 'employee_setup';

    protected $fillable = [
        'company_uid',
        'employee_id',
        'due_date',
        'salary',
        'remarks'
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_uid', 'company_uid');
    }

    public function payroll()
    {
        return $this->hasOne(Payroll::class, 'reference_id')->where('type', 'salary');
    }
}
