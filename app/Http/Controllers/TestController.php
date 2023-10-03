<?php

namespace App\Http\Controllers;

use App\Helpers\Promotion\Sales\ProductSaleHelper;
use App\Models\Category\Category;
use App\Models\Filter\Filter;
use App\Models\Filter\FilterGroup;
use App\Models\Product\Product;
use App\Models\Promotion\Sale;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode as SimpleQR;

class TestController extends Controller
{


    public function index()
    {


        $filterGroup = FilterGroup::with('filters','filters.options')->whereIn('id',[5,6])->get();
        $filterGroupId = $filterGroup->shuffle()->all()[0]->id;
        $selectedFilterGroup = $filterGroup->firstWhere('id',$filterGroupId);




        dd($bag);


        dd(
            $filterGroupId,
            $filterOptions
        );








//        $saleProducts = new ProductSaleHelper();
//        $saleProducts->reindexSaleableProducts();


    }


    /**
     * This give error :
     * BaconQrCode Exception RuntimeException PHP 8.1.12 10.13.2
     * You need to install the imagick extension to use this back end
     * @param $url
     * @param int $width
     * @param int $height
     * @return string
     */
    private function generateQRCodeViaSimpleSoftware($url, int $width = 100, int $height = 100)
    {
        $code = SimpleQR::generate($url);
        return 'data:image/svg+xml;base64,' . base64_encode($code);
    }




}
