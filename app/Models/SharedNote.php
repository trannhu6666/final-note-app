<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SharedNote extends Model
{
    public $timestamps = false; // Bảng này theo thiết kế không có created_at/updated_at mặc định
    protected $fillable = ['note_id', 'recipient_email', 'permission', 'shared_at'];
}
