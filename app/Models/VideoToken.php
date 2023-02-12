<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoToken extends Model
{
    use HasFactory;
    protected $table = 'videos_tokens';
    protected $primaryKey = 'id';
}
