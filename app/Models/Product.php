<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    
    const TYPE_NORMAL = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';
    const TYPE_SECKILL = 'seckill';
    public static $typeMap = [
        self::TYPE_NORMAL  => 'Normal product',
        self::TYPE_CROWDFUNDING => 'Crowdfunding goods',
        self::TYPE_SECKILL => 'Spike product',
    ];
    
    protected $fillable = [
        'title', 'description', 'image', 'on_sale',
        'rating', 'sold_count', 'review_count', 'price', 'type', 'long_title',
    ];
    
    protected $casts = [
        'on_sale' => 'boolean', 
    ];
    
    
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function crowdfunding()
    {
        return $this->hasOne(CrowdfundingProduct::class);
    }
    
    public function properties()
    {
        return $this->hasMany(ProductProperty::class);
    }
    
    public function seckill()
    {
        return $this->hasOne(SeckillProduct::class);
    }
    
    public function getImageUrlAttribute()
    {
        
         if (Str::startsWith($this->attributes['image'], ['http://', 'https://'])) {
            return $this->attributes['image'];
        }
        return \Storage::disk('public')->url($this->attributes['image']);
    }
    
    public function getGroupedPropertiesAttribute()
    {
        return $this->properties
            
            ->groupBy('name')
            ->map(function ($properties) {
                
                return $properties->pluck('value')->all();
            });
    }
    
    public function toESArray()
    {
        
        $arr = array_only($this->toArray(), [
            'id',
            'type',
            'title',
            'category_id',
            'long_title',
            'on_sale',
            'rating',
            'sold_count',
            'review_count',
            'price',
        ]);
        
        
        $arr['category'] = $this->category ? explode(' - ', $this->category->full_name) : '';
        
        $arr['category_path'] = $this->category ? $this->category->path : '';
        
        $arr['description'] = strip_tags($this->description);
        
        $arr['skus'] = $this->skus->map(function (ProductSku $sku) {
            return array_only($sku->toArray(), ['title', 'description', 'price']);
        });
        
        $arr['properties'] = $this->properties->map(function (ProductProperty $property) {
            
            return array_merge(array_only($property->toArray(), ['name', 'value']), [
                'search_value' => $property->name.':'.$property->value,
            ]);
        });
        
        return $arr;
    }
    
    public function scopeByIds($query, $ids)
    {
        return $query->whereIn('id', $ids)->orderByRaw(sprintf("FIND_IN_SET(id, '%s')", join(',', $ids)));
    }
}
