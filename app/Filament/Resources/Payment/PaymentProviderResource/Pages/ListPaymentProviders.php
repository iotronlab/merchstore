<?php

namespace App\Filament\Resources\Payment\PaymentProviderResource\Pages;

use App\Filament\Resources\Payment\PaymentProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentProviders extends ListRecords
{
    protected static string $resource = PaymentProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
