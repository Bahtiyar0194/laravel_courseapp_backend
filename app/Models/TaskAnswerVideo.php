<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAnswerVideo extends Model
{
    use HasFactory;
    protected $table = 'task_answer_videos';
    protected $primaryKey = 'task_answer_video_id';
}
