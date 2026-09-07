<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Classes;

class Student extends Model
{
    use HasFactory;

    protected $primaryKey = 'student_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'matrix_number',
        'program_code',
    ];

    /**
     * Relationship back to User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function classes()
    {
        return $this->belongsToMany(
            Classes::class,
            'class_student',
            'student_id',
            'class_id'
        );
    }
}