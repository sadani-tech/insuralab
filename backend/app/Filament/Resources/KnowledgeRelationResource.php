<?php

namespace App\Filament\Resources;

use App\Models\KnowledgeRelation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KnowledgeRelationResource extends Resource
{
    protected static ?string $model = KnowledgeRelation::class;

    protected static ?string $navigationLabel = 'Hubungan pengetahuan';

    protected static ?string $navigationGroup = 'Knowledge Explorer';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form->schema([Forms\Components\Select::make('from_id')->options(fn () => \App\Models\GlossaryTerm::pluck('term', 'id'))->searchable()->required(),
            Forms\Components\Select::make('to_id')->options(fn () => \App\Models\GlossaryTerm::pluck('term', 'id'))->searchable()->different('from_id')->required(),
            Forms\Components\TextInput::make('label')->required()->maxLength(255),
            Forms\Components\Textarea::make('explanation')->required()->rows(4)->columnSpanFull(),
            Forms\Components\Select::make('source_passage_id')->options(fn () => \App\Models\SourcePassage::pluck('locator', 'id'))->searchable()->required(),
            Forms\Components\Toggle::make('published')->default(false), ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('label')->searchable()->limit(80), Tables\Columns\TextColumn::make('explanation')->searchable()->limit(80), Tables\Columns\IconColumn::make('published')->boolean()])
            ->actions([Tables\Actions\EditAction::make()])
            ->filters([Tables\Filters\TernaryFilter::make('published')]);
    }

    public static function getPages(): array
    {
        return ['index' => KnowledgeRelationResource\Pages\ManageKnowledgeRelations::route('/')];
    }
}
