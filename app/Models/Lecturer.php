<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    use HasFactory;

    protected $primaryKey = 'lecturer_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'staff_number',
        'department',
    ];

    /**
     * Relationship back to User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'lecturer_id', 'lecturer_id');
    }

    /**
     * Relationship to Classes (A lecturer has many classes)
     */
    public function classes()
    {
        return $this->hasMany(Classes::class, 'lecturer_id', 'lecturer_id');
    }
}