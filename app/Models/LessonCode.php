<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonCode extends Model
{
    use HasFactory;
    protected $table = 'lesson_codes';
    protected $primaryKey = 'lesson_code_id';
}
