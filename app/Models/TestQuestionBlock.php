<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestQuestionBlock extends Model
{
    use HasFactory;
    protected $table = 'test_question_blocks';
    protected $primaryKey = 'test_question_block_id';
}
