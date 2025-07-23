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

    // Relación con códigos
    public function codes()
    {
        return $this->hasMany(PartCode::class)->orderBy('sort_order')->orderBy('is_primary', 'desc');
    }

    public function primaryCode()
    {
        return $this->hasOne(PartCode::class)->where('is_primary', true);
    }

    public function activeCodes()
    {
        return $this->hasMany(PartCode::class)->where('is_active', true)->orderBy('sort_order');
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

    //Scope para buscar en códigos también
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('part_number', 'like', "%{$search}%")
              ->orWhere('original_code', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhereHas('codes', function ($codeQuery) use ($search) {
                  $codeQuery->where('code', 'like', "%{$search}%")
                           ->where('is_active', true);
              });
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

    // Accessors para códigos
    public function getPrimaryCodeValueAttribute()
    {
        return $this->primaryCode?->code ?? $this->part_number ?? 'Sin código';
    }

    public function getAllCodesAttribute()
    {
        return $this->activeCodes->pluck('code')->toArray();
    }

    public function getFormattedCodesAttribute()
    {
        $codes = $this->activeCodes;
        if ($codes->isEmpty()) {
            return $this->part_number ?? 'Sin códigos';
        }

        return $codes->map(function ($code) {
            return $code->type === 'internal'
                ? $code->code
                : $code->code . ' (' . $code->formatted_type . ')';
        })->join(', ');
    }

    public function incrementViews()
    {
        $this->increment('view_count');
    }

    //métodos para manejar códigos
    public function addCode(string $code, string $type = 'internal', bool $isPrimary = false)
    {
        // Si es primary, quitar primary de otros códigos
        if ($isPrimary) {
            $this->codes()->update(['is_primary' => false]);
        }

        return $this->codes()->create([
            'code' => $code,
            'type' => $type,
            'is_primary' => $isPrimary,
            'is_active' => true,
            'sort_order' => $this->codes()->max('sort_order') + 1,
        ]);
    }

    public function updateCodes(array $codes)
    {
        // Eliminar códigos existentes que no estén en el array
        $newCodes = collect($codes)->pluck('code')->toArray();
        $this->codes()->whereNotIn('code', $newCodes)->delete();

        // Agregar/actualizar códigos
        foreach ($codes as $index => $codeData) {
            $this->codes()->updateOrCreate(
                ['code' => $codeData['code']],
                [
                    'type' => $codeData['type'] ?? 'internal',
                    'is_primary' => $codeData['is_primary'] ?? false,
                    'is_active' => $codeData['is_active'] ?? true,
                    'sort_order' => $index,
                ]
            );
        }

        // Asegurar que haya al menos un código primary
        if (!$this->codes()->where('is_primary', true)->exists()) {
            $this->codes()->first()?->update(['is_primary' => true]);
        }
    }
}
