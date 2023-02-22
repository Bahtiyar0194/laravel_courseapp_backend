<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonBlock extends Model
{
    use HasFactory;
    protected $table = 'lesson_blocks';
    protected $primaryKey = 'lesson_block_id';
}
