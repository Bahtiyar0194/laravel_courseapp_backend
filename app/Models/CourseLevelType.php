<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseLevelType extends Model
{
    use HasFactory;
    protected $table = 'types_of_course_level';
    protected $primaryKey = 'level_type_id';
}
