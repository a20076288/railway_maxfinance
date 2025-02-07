<?php

namespace App\Filament\Resources\FeriasResource\Pages;

use App\Filament\Resources\FeriasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFerias extends EditRecord
{
    protected static string $resource = FeriasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
