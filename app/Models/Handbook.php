<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $year
 * @property int $grade
 * @property string $semester
 * @property string $lesson
 * @property string|null $content
 * @property int $status
 * @property int $published_at
 * @property int $created_at
 * @property int $updated_at
 */
class Handbook extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'year',
        'grade',
        'semester',
        'lesson',
        'content',
        'status',
        'published_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'grade' => 'integer',
        'status' => 'integer',
        'published_at' => 'integer',
        'created_at' => 'integer',
        'updated_at' => 'integer',
    ];
}
