<?php

namespace Back\Models\Filter;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterGroup extends Model
{
    use HasFactory;

    public const STATIC = 'static';
    public const FILTERABLE = 'filterable';

    public const TYPE_OPTIONS = [
      self::STATIC => 'Static',
      self::FILTERABLE => 'Filterable'
    ];

    protected $fillable = [
        'admin_name',
        'code',
        'position',
        'type'
    ];


    public function attributes()
    {
        return $this->belongsToMany(Filter::class,'attribute_group_mappings', 'attribute_group_id', 'attribute_id');
    }

    public function product()
    {
        return $this->hasMany(Product::class);
    }


}
