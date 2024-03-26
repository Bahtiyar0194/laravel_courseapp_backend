<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestProperty extends Model
{
    use HasFactory;
    protected $table = 'test_properties';
    protected $primaryKey = 'property_id';
}
