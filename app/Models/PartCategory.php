<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PartCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Relaciones
    public function parent()
    {
        return $this->belongsTo(PartCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PartCategory::class, 'parent_id');
    }

    public function parts()
    {
        return $this->hasMany(Part::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeParent($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeChild($query)
    {
        return $query->whereNotNull('parent_id');
    }

    // Accessors
    public function getIsParentAttribute()
    {
        return is_null($this->parent_id);
    }

    public function getPartsCountAttribute()
    {
        return $this->parts()->count();
    }
}
