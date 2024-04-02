<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAnswerCode extends Model
{
    use HasFactory;
    protected $table = 'task_answer_codes';
    protected $primaryKey = 'task_answer_code_id';
}
