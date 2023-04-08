<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskVideo extends Model
{
    use HasFactory;
    protected $table = 'task_videos';
    protected $primaryKey = 'task_video_id';
}
