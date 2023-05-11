<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSuitable extends Model
{
    use HasFactory;
    protected $table = 'course_suitables';
    protected $primaryKey = 'id';
}
