<?php

namespace App\Filament\Resources\KnowledgeRelationResource\Pages;

use App\Filament\Resources\KnowledgeRelationResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageKnowledgeRelations extends ManageRecords
{
    protected static string $resource = KnowledgeRelationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
