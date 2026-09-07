<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $table = 'classes';
    protected $primaryKey = 'class_id';

    protected $fillable = [
        'lecturer_id', 
        'class_name', 
        'class_code',
        'code',
    ];

    /**
     * Relationship: Class belongs to a Lecturer
     */
    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }

    /**
     * Relationship: Class belongs to many Students (via pivot table)
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'class_student', 'class_id', 'student_id');
    }
}