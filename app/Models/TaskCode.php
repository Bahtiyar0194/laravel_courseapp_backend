<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskCode extends Model
{
    use HasFactory;
    protected $table = 'task_codes';
    protected $primaryKey = 'task_code_id';
}
