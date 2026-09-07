<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $primaryKey = 'exam_id';

    protected $fillable = [
        'lecturer_id',
        'course_id',
        'class_id',
        'title',
        'duration_minutes',
        'start_time',
        'end_time',
        'is_randomized',
    ];

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id', 'class_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    public function questions()
    {
        return $this->hasMany(
            Question::class,
            'exam_id',
            'exam_id'
        );
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class, 'exam_id', 'exam_id');
    }
}