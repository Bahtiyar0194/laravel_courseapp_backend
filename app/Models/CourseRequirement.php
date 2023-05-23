<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseRequirement extends Model
{
    use HasFactory;
    protected $table = 'course_requirements';
    protected $primaryKey = 'item_id';
}
