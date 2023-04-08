<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskBlock extends Model
{
    use HasFactory;
    protected $table = 'task_blocks';
    protected $primaryKey = 'task_block_id';
}