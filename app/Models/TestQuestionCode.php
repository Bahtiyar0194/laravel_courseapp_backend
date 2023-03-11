<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestQuestionCode extends Model
{
    use HasFactory;
    protected $table = 'test_question_codes';
    protected $primaryKey = 'test_question_code_id';
}
