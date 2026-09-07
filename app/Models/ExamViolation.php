<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamViolation extends Model
{
    use HasFactory;

    protected $primaryKey = 'violation_id';

    protected $fillable = [
        'exam_id',
        'student_id',
        'violation_type',
        'occurred_at',
    ];

    protected $table = 'exam_violations';

    /**
     * Relationship to the Exam model
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'exam_id');
    }

    /**
     * Relationship to the Student model
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}