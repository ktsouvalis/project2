<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    
    protected $table = 'courses';
    protected $fillable = ['name', 'open_seats'];

    public function users()
    {
        return $this->hasManyThrough(User::class, UserCourse::class, 'course_id', 'id', 'id', 'user_id');
    }
}
