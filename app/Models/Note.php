<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = ['user_id', 'title', 'content', 'is_pinned', 'note_password_hash'];

    public function images()
    {
        return $this->hasMany(NoteImage::class);
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class, 'note_label');
    }
}
