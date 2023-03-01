<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonImage extends Model
{
    use HasFactory;
    protected $table = 'lesson_images';
    protected $primaryKey = 'lesson_image_id';
}
