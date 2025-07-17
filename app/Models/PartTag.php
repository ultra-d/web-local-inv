<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
    ];

    // Relaciones
    public function parts()
    {
        return $this->belongsToMany(Part::class, 'part_tag', 'tag_id', 'part_id');
    }

    // Accessors
    public function getPartsCountAttribute()
    {
        return $this->parts()->count();
    }
}
