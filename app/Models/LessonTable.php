<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonTable extends Model
{
    use HasFactory;
    protected $table = 'lesson_tables';
    protected $primaryKey = 'lesson_table_id';
}
