<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskTable extends Model
{
    use HasFactory;
    protected $table = 'task_tables';
    protected $primaryKey = 'task_table_id';
}
