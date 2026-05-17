<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Thêm dòng này

class User extends Authenticatable
{
    // Đảm bảo có HasApiTokens ở đây
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'display_name',
        'password_hash',
        'avatar_url',
        'is_active',
        'theme_pref',
        'font_size_pref',
    ];

    // Ẩn field password_hash khi trả về API cho an toàn
    protected $hidden = [
        'password_hash',
    ];

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
    
    public function labels()
    {
        return $this->hasMany(Label::class);
    }
}