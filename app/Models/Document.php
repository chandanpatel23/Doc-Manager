<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'document_type',
        'order_no',
        'order_date',
        'thumbnail',
        'remarks',
        'filename',
        'mime_type',
        'size',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'order_date' => 'date',
    ];

    // return thumbnail url if exists, else return file url
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail && \Illuminate\Support\Facades\Storage::exists($this->thumbnail)) {
            return \Illuminate\Support\Facades\Storage::url($this->thumbnail);
        }

        return null;
    }
}
