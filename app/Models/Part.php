<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_id',
        'category_id',
        'name',
        'part_number',
        'original_code',
        'brand',
        'price',
        'stock_quantity',
        'min_stock',
        'location',
        'image_path',
        'description',
        'notes',
        'is_bestseller',
        'is_available',
        'view_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock' => 'integer',
        'is_bestseller' => 'boolean',
        'is_available' => 'boolean',
        'view_count' => 'integer',
    ];

    // Relaciones
    public function model()
    {
        return $this->belongsTo(VehicleModel::class, 'model_id');
    }

    public function category()
    {
        return $this->belongsTo(PartCategory::class, 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(PartTag::class, 'part_tag', 'part_id', 'tag_id');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeBestseller($query)
    {
        return $query->where('is_bestseller', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock');
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByModel($query, $modelId)
    {
        return $query->where('model_id', $modelId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('part_number', 'like', "%{$search}%")
              ->orWhere('original_code', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function getStockStatusAttribute()
    {
        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        } elseif ($this->stock_quantity <= $this->min_stock) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    public function getStockStatusColorAttribute()
    {
        return match($this->stock_status) {
            'out_of_stock' => 'red',
            'low_stock' => 'yellow',
            'in_stock' => 'green',
            default => 'gray'
        };
    }

    public function getFormattedPriceAttribute()
    {
        return $this->price ? '$' . number_format($this->price, 2) : 'N/A';
    }

    // Métodos
    public function incrementViews()
    {
        $this->increment('view_count');
    }
}
