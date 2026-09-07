<?php

namespace App\Filament\Resources;

use App\Enums\ContentLevel;
use App\Enums\ContentStatus;
use App\Filament\Resources\LearningPathResource\Pages;
use App\Models\LearningPath;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LearningPathResource extends Resource
{
    protected static ?string $model = LearningPath::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Learning';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Learning path')->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set, ?LearningPath $record) => $record ? null : $set('slug', \Illuminate\Support\Str::slug((string) $state))),
                Forms\Components\TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                Forms\Components\Select::make('level')->options(ContentLevel::class)->required()->native(false),
                Forms\Components\TextInput::make('estimated_duration_minutes')->numeric()->minValue(1)->suffix('minutes'),
                Forms\Components\Select::make('status')->options(ContentStatus::class)->default(ContentStatus::Draft)->required()->native(false),
                Forms\Components\TextInput::make('sort_order')->numeric()->minValue(0)->default(0)->required(),
                Forms\Components\Textarea::make('description')->required()->rows(3)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Repeater::make('items')
                ->relationship()->orderColumn('position')->collapsible()->reorderable()
                ->itemLabel(fn (array $state): string => ucfirst($state['item_type'] ?? 'item'))
                ->schema([
                    Forms\Components\Select::make('item_type')
                        ->options(['article' => 'Article', 'quiz' => 'Quiz'])
                        ->default('article')->required()->live()->native(false),
                    Forms\Components\Select::make('article_id')
                        ->label('Article')
                        ->relationship('article', 'title', modifyQueryUsing: fn ($query) => $query->general()->public())
                        ->searchable()->preload()
                        ->visible(fn (Forms\Get $get): bool => $get('item_type') === 'article')
                        ->required(fn (Forms\Get $get): bool => $get('item_type') === 'article'),
                    Forms\Components\Select::make('quiz_id')
                        ->label('Quiz')
                        ->relationship('quiz', 'title', modifyQueryUsing: fn ($query) => $query->published())
                        ->searchable()->preload()
                        ->visible(fn (Forms\Get $get): bool => $get('item_type') === 'quiz')
                        ->required(fn (Forms\Get $get): bool => $get('item_type') === 'quiz'),
                    Forms\Components\Textarea::make('note')->rows(2)->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->weight('medium'),
                Tables\Columns\TextColumn::make('level')->badge(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('items_count')->counts('items')->label('Items'),
                Tables\Columns\TextColumn::make('estimated_duration_minutes')->suffix(' min'),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([Tables\Filters\SelectFilter::make('status')->options(ContentStatus::class)])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLearningPaths::route('/'),
            'create' => Pages\CreateLearningPath::route('/create'),
            'edit' => Pages\EditLearningPath::route('/{record}/edit'),
        ];
    }
}
