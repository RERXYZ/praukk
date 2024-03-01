<?php

namespace App\Filament\Resources\ProdukResource\Pages;

use Filament\Actions;
use App\Filament\Resources\ProdukResource;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\ProdukResource\Widgets\ProdukStatsOverview;

class ManageProduks extends ManageRecords
{
    protected static string $resource = ProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
