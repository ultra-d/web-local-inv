<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'code',
        'type',
        'is_primary',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relaciones
    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('code', 'like', "%{$search}%");
    }

    // Accessors
    public function getTypeColorAttribute()
    {
        return match($this->type) {
            'internal' => 'blue',
            'oem' => 'purple',
            'aftermarket' => 'yellow',
            'universal' => 'gray',
            default => 'gray'
        };
    }

    public function getTypeIconAttribute()
    {
        return match($this->type) {
            'internal' => '🏢',
            'oem' => '⚙️',
            'aftermarket' => '🔧',
            'universal' => '🌐',
            default => '📦'
        };
    }

    public function getFormattedTypeAttribute()
    {
        return match($this->type) {
            'internal' => 'Interno',
            'oem' => 'OEM',
            'aftermarket' => 'Aftermarket',
            'universal' => 'Universal',
            default => $this->type
        };
    }

    // Métodos estáticos
    public static function getTypes()
    {
        return [
            'internal' => 'Interno',
            'oem' => 'OEM',
            'aftermarket' => 'Aftermarket',
            'universal' => 'Universal',
        ];
    }

    // Métodos
    public function setPrimary()
    {
        // Quitar primary de otros códigos del mismo repuesto
        self::where('part_id', $this->part_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        // Establecer este como primary
        $this->update(['is_primary' => true]);
    }
}
