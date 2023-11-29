<?php

namespace App\Services\ShippingService\Contracts;

use App\Services\ShippingService\Contracts\Provider\ShippingProviderActionContract;
use App\Services\ShippingService\Contracts\Provider\ShippingProviderCourierContract;
use App\Services\ShippingService\Contracts\Provider\ShippingProviderTrackingContract;

interface ShippingProviderContract
{

    public function getProviderName(): string;
    public function getClass():string;

    public function setError(string $error):void;
    public function getError():?string;

    public function order():ShippingProviderActionContract;
    public function courier():ShippingProviderCourierContract;
    public function return();
    public function shipment();
    public function tracking():ShippingProviderTrackingContract;

}
