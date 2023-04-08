<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAudio extends Model
{
    use HasFactory;
    protected $table = 'task_audios';
    protected $primaryKey = 'task_audio_id';
}