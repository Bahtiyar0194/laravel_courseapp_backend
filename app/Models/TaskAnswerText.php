<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAnswerText extends Model
{
    use HasFactory;
    protected $table = 'task_answer_texts';
    protected $primaryKey = 'task_answer_text_id';
}
