<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoteImage extends Model
{
    protected $fillable = ['note_id', 'image_url'];

    public $timestamps = false;

    /**
     * Mối quan hệ giữa Ảnh và Note
     */
    public function note()
    {
        return $this->belongsTo(Note::class);
    }
}