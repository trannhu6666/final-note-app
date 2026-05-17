<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id'];

    public function notes()
    {
        // Khai báo mối quan hệ Nhiều-Nhiều (N-N) thông qua bảng trung gian note_label
        return $this->belongsToMany(Note::class, 'note_label');
    }
}