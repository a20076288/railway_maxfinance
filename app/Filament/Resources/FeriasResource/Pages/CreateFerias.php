<?php

namespace App\Filament\Resources\FeriasResource\Pages;

use App\Filament\Resources\FeriasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFerias extends CreateRecord
{
    protected static string $resource = FeriasResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
