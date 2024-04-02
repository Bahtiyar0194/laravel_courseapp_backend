<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAnswerBlock extends Model
{
    use HasFactory;
    protected $table = 'task_answer_blocks';
    protected $primaryKey = 'task_answer_block_id';
}
