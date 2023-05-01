<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaFileType extends Model{
    use HasFactory;
    protected $table = 'types_of_media_files';
    protected $primaryKey = 'file_type_id';
}