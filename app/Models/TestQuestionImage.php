<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestQuestionImage extends Model
{
    use HasFactory;
    protected $table = 'test_question_images';
    protected $primaryKey = 'test_question_image_id';
}
