<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use drh2so4\Thumbnail\Traits\thumbnail;
use Spatie\MediaLibrary\Models\Media;
class TempImage extends Model
{
    use HasFactory;
    use Thumbnail;

    protected $fillable = ['image'];

    // public function registerMediaConversions(Media $media = null)
    // {
    //     $this->addMediaConversion('thumb')
    //          ->width(368)
    //          ->height(232)
    //          ->sharpen(10);
    // }

}