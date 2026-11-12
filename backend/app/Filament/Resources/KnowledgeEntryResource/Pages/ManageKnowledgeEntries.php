<?php

namespace App\Filament\Resources\KnowledgeEntryResource\Pages;

use App\Filament\Resources\KnowledgeEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageKnowledgeEntries extends ManageRecords
{
    protected static string $resource = KnowledgeEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
