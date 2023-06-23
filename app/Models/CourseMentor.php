<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseMentor extends Model
{
    use HasFactory;
    protected $table = 'courses_mentors';
    protected $primaryKey = 'id';
}
