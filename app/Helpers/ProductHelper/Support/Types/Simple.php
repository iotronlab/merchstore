<?php

namespace App\Helpers\ProductHelper\Support\Types;

use App\Helpers\ProductHelper\Support\AbstractSupportProductSupport;
use App\Models\Filter\FilterGroup;
use App\Models\Product\Product;

class Simple extends AbstractSupportProductSupport
{
    public function create(array $data): bool|Product
    {

        // Create Product
        $product = parent::create($data); // TODO: Change the autogenerated stub

        // Fill Filter Attributes
        $group = FilterGroup::where(['id' => $product->filter_group_id])->with('filters')->first();
        foreach ($group->filters as $attribute) {
            $data['filter_attributes'][$attribute->code] = null;
        }
        $product->flat()->create([
            'sku' => $data['sku'],
            'filter_attributes' => $data['filter_attributes'],
        ]);

        return $product;
    }

    public function isSaleable(): bool
    {
        // TODO: Implement isSaleable() method.
    }

    public function haveSufficientQuantity(): int
    {
        // TODO: Implement haveSufficientQuantity() method.
    }
}
