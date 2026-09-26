<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileAuditLog extends Model
{
    use HasFactory;

    protected $table = 'profile_audit_logs';

    // Allow all mass assignment fields
    protected $guarded = [];
}