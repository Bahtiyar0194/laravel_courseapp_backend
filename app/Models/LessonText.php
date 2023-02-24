<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonText extends Model
{
    use HasFactory;
    protected $table = 'lesson_texts';
    protected $primaryKey = 'lesson_text_id';
}
