<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAnswerImage extends Model
{
    use HasFactory;
    protected $table = 'task_answer_images';
    protected $primaryKey = 'task_answer_image_id';
}
