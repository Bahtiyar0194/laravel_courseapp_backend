<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAnswerAudio extends Model
{
    use HasFactory;
    protected $table = 'task_answer_audios';
    protected $primaryKey = 'task_answer_audio_id';
}
