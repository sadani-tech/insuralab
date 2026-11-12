<?php

namespace App\Filament\Resources\SourcePassageResource\Pages;

use App\Filament\Resources\SourcePassageResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSourcePassages extends ManageRecords
{
    protected static string $resource = SourcePassageResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
