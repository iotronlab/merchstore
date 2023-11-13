<?php

namespace App\Helpers\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
interface CanBeFilterableContract
{

    public function getFilterScopes():array;

    public function getFilterOptions(): JsonResponse|AnonymousResourceCollection|array;

}
