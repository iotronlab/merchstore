<?php

namespace App\Http\Controllers\Api;


use App\Helpers\Contracts\CanBeFilterableContract;
use App\Helpers\Contracts\CanBeSortableContract;
use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductIndexResource;
use App\Http\Resources\Product\ProductResource;
use App\Models\Filter\FilterOption;
use App\Models\Product\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Category\ThemeResource;
use App\Http\Resources\Filter\FilterIndexResource;
use App\Models\Category\Category;
use App\Models\Category\Theme;
use App\Models\Filter\FilterGroup;
use App\Scoping\Scopes\CategoryScope;
use App\Scoping\Scopes\ColorScope;
use App\Scoping\Scopes\FilterScope;
use App\Scoping\Scopes\MaterialScope;
use App\Scoping\Scopes\MediumScope;
use App\Scoping\Scopes\OrientationScope;
use App\Scoping\Scopes\SizeScope;
use App\Scoping\Scopes\ThemeScope;
use App\Scoping\Scopes\TypeScope;
use App\Scoping\Scopes\ViewScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller implements CanBeSortableContract
{




    public function getFilterScopes(?string $scope_name = null): array
    {
        $allScopes = [
            'filters' => new FilterScope(),
            'themes' => new ThemeScope(),
            'categories' => new CategoryScope(),
        ];
        return is_null($scope_name) ? $allScopes : $allScopes[$scope_name];
    }



    public function getSortingOptions(): array
    {
        return [

            [
                'name' => 'popularity',
                'value' => 'view_count',
                'direction' => 'desc'
            ],
            [
                'name' => 'latest',
                'value' => 'created_at',
                'direction' => 'desc'
            ],
            [
                'name' => 'pricelow2high',
                'value' => 'price',
                'direction' => 'asc'
            ],
            [
                'name' => 'pricehigh2low',
                'value' => 'price',
                'direction' => 'desc'
            ]
        ];
    }

    public function getCurrentSort(Request $request): array
    {
        $allOptions = collect($this->getSortingOptions());
        if ($request->sort) {
            $sortKey = key($request->sort);


            $matchingSort = $allOptions->first(function ($sort) use ($sortKey) {
                return $sort['name'] === $sortKey;
            });

            return $matchingSort ?? [];
        }
        return $defaultSort = $allOptions->first(function ($sort) {
            return $sort['value'] === 'created_at';
        });
    }




    protected function scopes()
    {
        return [
            'filters' => new FilterScope(),
            'themes' => new ThemeScope(),
            'categories' => new CategoryScope(),
        ];
    }



    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('flat', 'media', 'filterOptions', 'filterOptions.filter', 'themes', 'feedbacks');
        return ProductResource::make($product);
    }

    public function showProductsByCategory(Category $category, Request $request)
    {
        $category->load('children', 'media');
        // base query
        $query = Product::with([
            'media',
            'availableStocks'
        ])->where('status', Product::PUBLISHED)->whereHas('categories', function ($query) use ($category) {
            $query->with('media')->where('categories.id', $category->id);
        });
        // finding all the filters before pagination
        $filterGroupIds = $query->pluck('filter_group_id')->unique()->toArray();
        $filterGroups = FilterGroup::whereIn('id', $filterGroupIds)->with('filters.options')->get();
        // Remove duplicates from the filters relationship and flattens structure
        $filters = $filterGroups->flatMap(function ($filterGroup) {
            return $filterGroup->filters;
        })->unique('id');


        // get product themes
        $themes = $query
            ->with('themes')
            ->get()
            ->pluck('themes')
            ->flatten();

        // additional information
        // $query->with('media');




        $query->withScopes($this->scopes());

        //


        //
//        // If Request has Filter
//        if (isset($request->filter)) {
//            $query->withScopes($this->getFilterScopes());
//        }

        $currentSort = $this->getCurrentSort($request);


        // If Request Has Sort
        if (isset($request->sort)) {
            if (!empty($currentSort)) {

                // Apply Sort
                if ($currentSort['direction']) {
                    $query->orderBy($currentSort['value'], $currentSort['direction']);
                } else {
                    $query->orderBy($currentSort['value'], 'desc');
                }
            }
        } else {
            $query->orderBy($currentSort['value'], $currentSort['direction']);
        }





        $products = $query->paginate();
        return ProductIndexResource::collection($products)
            ->additional([
                'categories' => CategoryResource::collection($category->children),
                'filters' => FilterIndexResource::collection($filters),
                'themes' => ThemeResource::collection($themes)
            ]);
    }

    public function showProductsByTheme(Request $request, Theme $theme)
    {

        $theme->load('children');
        $query = Product::where('status', Product::PUBLISHED)
            ->whereHas('themes', function ($query) use ($theme) {
                $query->where('themes.id', $theme->id);
            });
        // finding all the filters before pagination
        $filterGroupIds = $query->pluck('filter_group_id')->unique()->toArray();
        $filterGroups = FilterGroup::whereIn('id', $filterGroupIds)->with('filters.options')->get();
        // Remove duplicates from the filters relationship and flattens structure
        $filters = $filterGroups->flatMap(function ($filterGroup) {
            return $filterGroup->filters;
        })->unique('id');


        // get product categories
        $categories = $query
            ->with('categories')
            ->get()
            ->pluck('categories')
            ->flatten();

        // additional information
        $query->with('media');


        // If Request has Filter
        if (isset($request->filter)) {
            $query->withScopes($this->getFilterScopes());
        }

        $currentSort = $this->getCurrentSort($request);


        // If Request Has Sort
        if (isset($request->sort)) {
            if (!empty($currentSort)) {

                // Apply Sort
                if ($currentSort['direction']) {
                    $query->orderBy($currentSort['value'], $currentSort['direction']);
                } else {
                    $query->orderBy($currentSort['value'], 'desc');
                }
            }
        } else {
            $query->orderBy($currentSort['value'], $currentSort['direction']);
        }






        $products = $query->paginate();

        return ProductIndexResource::collection($products)->additional([
            'categories' => CategoryResource::collection($categories),
            'filters' => FilterIndexResource::collection($filters),
            'themes' => ThemeResource::collection($theme->children)
        ]);
        ;
    }


}
