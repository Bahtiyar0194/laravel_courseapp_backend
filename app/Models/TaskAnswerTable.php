<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAnswerTable extends Model
{
    use HasFactory;
    protected $table = 'task_answer_tables';
    protected $primaryKey = 'task_answer_table_id';
}
