<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseInvite extends Model
{
    use HasFactory;
    protected $table = 'courses_invites';
    protected $primaryKey = 'id';
}
