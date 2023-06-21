<?php

namespace App\Helpers\ProductHelper\Support\Types;

use App\Helpers\ProductHelper\Support\AbstractSupportProductSupport;
use App\Models\Product\Product;
use Illuminate\Support\Str;

class Configurable extends AbstractSupportProductSupport
{



    public function create(array $data): bool|Product
    {
        // Create Parent Product
        $product = parent::create($data);
        // Create Product Flat
        $productFlat = $product->flat()->create([
            'sku' => $data['sku'],
            'filter_attributes' => $data['filter_attributes'],
        ]);

        // If Multiple Attributes Present
        return $this->createMultipleVariants($data,$product);
    }


    private function createMultipleVariants(array $data,Product $product): Product
    {
        // Multiple Case
        $allFilterable = $this->array_permutation($data['filter_attributes']);

        $dataBag = [];
        foreach ($allFilterable as $key => $permutation) {
            $dataBag[$key]['name'] = $product->sku;
            $dataBag[$key]['url_key'] = Str::slug($product->sku.'-variant-'.implode('-', $permutation)).'-'.now();
            $dataBag[$key]['sku'] = $product->sku.'-variant-'.implode('-', $permutation);
            $dataBag[$key]['attribute_group_id'] = $product->attribute_group_id;
            $dataBag[$key]['type'] = 'simple';
//            $dataBag[$key]['vendor_id'] = $product->vendor_id;
            $dataBag[$key]['product_id'] = $product->id;
            $dataBag[$key]['filter_attributes'] = $permutation;
        }
        $variants = $product->variants()->createMany($dataBag);
        $variants->each(function ($item, $key) use ($dataBag) {
            $item->flat()->create($dataBag[$key]);
        });

        return $product;
    }


    /**
     * @param $input
     * @return array
     */
    public function array_permutation($input): array
    {
        $results = [];

        foreach ($input as $key => $values) {
            if (empty($values)) {
                continue;
            }

            if (empty($results)) {
                foreach ($values as $value) {
                    $results[] = [$key => $value];
                }
            } else {
                $append = [];

                foreach ($results as &$result) {
                    $result[$key] = array_shift($values);

                    $copy = $result;

                    foreach ($values as $item) {
                        $copy[$key] = $item;
                        $append[] = $copy;
                    }

                    array_unshift($values, $result[$key]);
                }

                $results = array_merge($results, $append);
            }
        }

        return $results;
    }





    /**
     * @return bool
     */
    public function isSaleable(): bool
    {
        // TODO: Implement isSaleable() method.
    }

    /**
     * @return int
     */
    public function haveSufficientQuantity(): int
    {
        // TODO: Implement haveSufficientQuantity() method.
    }


}
