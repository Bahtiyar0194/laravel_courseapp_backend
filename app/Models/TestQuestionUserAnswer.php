<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestQuestionUserAnswer extends Model
{
    use HasFactory;
    protected $table = 'test_question_user_answers';
    protected $primaryKey = 'id';
}
