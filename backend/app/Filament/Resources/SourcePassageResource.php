<?php

namespace App\Filament\Resources;

use App\Models\SourcePassage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SourcePassageResource extends Resource
{
    protected static ?string $model = SourcePassage::class;

    protected static ?string $navigationLabel = 'Versi & passage sumber';

    protected static ?string $navigationGroup = 'Knowledge Explorer';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form->schema([Forms\Components\TextInput::make('key')->required()->unique(ignoreRecord: true)->maxLength(255),
            Forms\Components\Select::make('source_id')->relationship('source', 'title')->required()->searchable()->preload(),
            Forms\Components\TextInput::make('version')->required()->maxLength(255),
            Forms\Components\TextInput::make('locator')->label('Halaman / bagian')->required()->maxLength(255),
            Forms\Components\Textarea::make('body')->required()->rows(10)->columnSpanFull(),
            Forms\Components\Toggle::make('published')->default(false)->helperText('Sumber harus berstatus public. Ubah key saat membuat versi baru agar citation lama stabil.'), ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('key')->searchable()->limit(80), Tables\Columns\TextColumn::make('locator')->searchable()->limit(80), Tables\Columns\TextColumn::make('version')->searchable()->limit(80), Tables\Columns\IconColumn::make('published')->boolean()])
            ->actions([Tables\Actions\EditAction::make()])
            ->filters([Tables\Filters\TernaryFilter::make('published')]);
    }

    public static function getPages(): array
    {
        return ['index' => SourcePassageResource\Pages\ManageSourcePassages::route('/')];
    }
}
