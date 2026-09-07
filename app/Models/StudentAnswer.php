<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    protected $table = 'student_answers';

    // 1. Tell Eloquent your actual primary key column name
    protected $primaryKey = 'answer_id'; // Replace 'answer_id' with your actual column name if different

    protected $fillable = [
        'attempt_id',
        'question_id',
        'selected_option_id',
        'answer_text',
    ];
}