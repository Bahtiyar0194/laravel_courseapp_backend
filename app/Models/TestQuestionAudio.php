<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestQuestionAudio extends Model
{
    use HasFactory;
    protected $table = 'test_question_audios';
    protected $primaryKey = 'test_question_audio_id';
}
