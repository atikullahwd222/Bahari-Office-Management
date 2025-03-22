<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $table = 'servers';
    protected $fillable = [
        'name',
        'hostname',
        'ip',
        'port',
        'username',
        'password',
        'license',
        'company_uid',
        'control_panel',
        'status',
    ];
    
}
