<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $hash
 * @property string $path
 * @property string $filename
 * @property string $mime_type
 * @property int $size
 * @property int $width
 * @property int $height
 * @property array|null $tags
 * @property int $created_at
 * @property int $updated_at
 */
class Photo extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'hash',
        'path',
        'filename',
        'mime_type',
        'size',
        'width',
        'height',
        'tags',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'tags' => 'array',
        'created_at' => 'integer',
        'updated_at' => 'integer',
    ];
}
