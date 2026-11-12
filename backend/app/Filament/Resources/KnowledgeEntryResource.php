<?php

namespace App\Filament\Resources;

use App\Models\KnowledgeEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KnowledgeEntryResource extends Resource
{
    protected static ?string $model = KnowledgeEntry::class;

    protected static ?string $navigationLabel = 'Materi terkurasi';

    protected static ?string $navigationGroup = 'Knowledge Explorer';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form->schema([Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
            Forms\Components\Select::make('kind')->options(['faq' => 'FAQ', 'guide' => 'Panduan'])->required(),
            Forms\Components\TextInput::make('title')->required()->maxLength(255),
            Forms\Components\Textarea::make('summary')->required()->columnSpanFull(),
            Forms\Components\Textarea::make('body')->required()->rows(7)->columnSpanFull(),
            Forms\Components\TagsInput::make('keywords'),
            Forms\Components\TagsInput::make('steps')->helperText('Urutan checklist panduan. Kosongkan untuk FAQ.'),
            Forms\Components\Select::make('source_passage_id')->options(fn () => \App\Models\SourcePassage::with('source')->get()->mapWithKeys(fn ($p) => [$p->id => $p->source->title.' · '.$p->locator]))->searchable()->required(),
            Forms\Components\Select::make('terms')->relationship('terms', 'term')->multiple()->searchable()->preload(),
            Forms\Components\Toggle::make('published')->default(false)->helperText('Hanya tampil bila passage dan sumber juga diizinkan untuk publik.'), ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('title')->searchable()->limit(80), Tables\Columns\TextColumn::make('kind')->searchable()->limit(80), Tables\Columns\IconColumn::make('published')->boolean()])
            ->actions([Tables\Actions\EditAction::make()])
            ->filters([Tables\Filters\TernaryFilter::make('published')]);
    }

    public static function getPages(): array
    {
        return ['index' => KnowledgeEntryResource\Pages\ManageKnowledgeEntries::route('/')];
    }
}
