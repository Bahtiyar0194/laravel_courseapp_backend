<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestQuestionAnswer extends Model
{
    use HasFactory;
    protected $table = 'test_question_answers';
    protected $primaryKey = 'answer_id';
}
