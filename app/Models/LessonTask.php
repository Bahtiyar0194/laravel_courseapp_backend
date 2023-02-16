<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonTask extends Model
{
    use HasFactory;
    protected $table = 'lesson_tasks';
    protected $primaryKey = 'task_id';
}
