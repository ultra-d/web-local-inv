<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VehicleModel extends Model
{
    use HasFactory;

    protected $table = 'models';

    protected $fillable = [
        'brand_id',
        'name',
        'code',
        'slug',
        'year_from',
        'year_to',
        'image_path',
        'description',
        'is_popular',
        'is_active',
    ];

    protected $casts = [
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'year_from' => 'integer',
        'year_to' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name . '-' . $model->code);
            }
        });
    }

    // Relaciones
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function parts()
    {
        return $this->hasMany(Part::class, 'model_id');
    }

    public function availableParts()
    {
        return $this->hasMany(Part::class, 'model_id')->where('is_available', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function getYearRangeAttribute()
    {
        if ($this->year_from && $this->year_to) {
            return $this->year_from . '-' . $this->year_to;
        } elseif ($this->year_from) {
            return $this->year_from . '+';
        }
        return 'N/A';
    }

    public function getPartsCountAttribute()
    {
        return $this->parts()->count();
    }
}
