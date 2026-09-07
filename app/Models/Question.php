<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'questions';

    protected $primaryKey = 'question_id';

    protected $fillable = [
        'exam_id',
        'question_text',
        'question_type',
        'points',
        'correct_answer_text',
    ];

    public function exam()
    {
        return $this->belongsTo(
            Exam::class,
            'exam_id',
            'exam_id'
        );
    }

    public function options()
    {
        return $this->hasMany(
            QuestionOption::class,
            'question_id',
            'question_id'
        );
    }
}