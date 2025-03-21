<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServerCommands extends Model
{
    protected $table = 'commands';
    protected $fillable = ['name', 'description', 'full_command', 'licensing_system', 'os'];
}
