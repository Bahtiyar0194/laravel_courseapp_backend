<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskText extends Model
{
    use HasFactory;
    protected $table = 'task_texts';
    protected $primaryKey = 'task_text_id';
}
