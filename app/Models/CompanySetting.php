<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\User;

class CompanySetting extends Model
{
    protected $table = 'company_settings';

    protected $fillable = [
        'company_name',
        'company_email',
        'company_phone',
        'company_address',
        'company_city',
        'company_state',
        'company_logo',
        'company_favicon',
        'company_website',
        'company_uid',
        'company_facebook',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->company_uid) {
                $model->company_uid = (string) Str::uuid();
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class, 'company_uid', 'company_uid');
    }
}
