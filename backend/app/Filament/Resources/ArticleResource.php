<?php

namespace App\Filament\Resources;

use App\Enums\ContentLevel;
use App\Enums\ContentStatus;
use App\Enums\ReferenceStatus;
use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Knowledge';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Knowledge type')
                ->description('General knowledge has no provider. Choosing a provider makes this provider-specific educational content, which requires review metadata before it can be published.')
                ->schema([
                    Forms\Components\Radio::make('knowledge_type')
                        ->label('This article is')
                        ->options([
                            'general' => 'General insurance knowledge',
                            'provider' => 'Provider-specific (case study)',
                        ])
                        ->default(fn (?Article $record) => $record?->provider_id ? 'provider' : 'general')
                        ->live()
                        ->dehydrated(false)
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state === 'general') {
                                $set('provider_id', null);
                            }
                        })
                        ->inline(),

                    Forms\Components\Select::make('provider_id')
                        ->relationship('provider', 'name')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->visible(fn (Get $get) => $get('knowledge_type') === 'provider')
                        ->required(fn (Get $get) => $get('knowledge_type') === 'provider')
                        ->helperText('provider_id = NULL means general insurance knowledge (ADR-0002).'),
                ]),

            Forms\Components\Section::make('Content')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Forms\Set $set, ?Article $record) => $record ? null : $set('slug', \Illuminate\Support\Str::slug((string) $state))),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Forms\Components\Select::make('category_id')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->native(false),
                    Forms\Components\Textarea::make('excerpt')
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('body')
                        ->required()
                        ->rows(18)
                        ->helperText('Markdown. Follow the CONTENT_GUIDE hierarchy: Concept → Example → Common misconception → Things to check → Source.')
                        ->columnSpanFull(),
                    Forms\Components\Select::make('tags')
                        ->relationship('tags', 'name')
                        ->multiple()
                        ->preload()
                        ->native(false),
                ])->columns(2),

            Forms\Components\Section::make('Publication')
                ->schema([
                    Forms\Components\Select::make('level')
                        ->options(ContentLevel::class)
                        ->default(ContentLevel::Beginner)
                        ->required()
                        ->native(false),
                    Forms\Components\Select::make('status')
                        ->options(ContentStatus::class)
                        ->default(ContentStatus::Draft)
                        ->required()
                        ->native(false),
                    Forms\Components\Toggle::make('is_featured'),
                    Forms\Components\TextInput::make('estimated_reading_minutes')
                        ->numeric()
                        ->minValue(1)
                        ->default(3)
                        ->required(),
                    Forms\Components\DateTimePicker::make('published_at')
                        ->native(false)
                        ->helperText('Left blank, it is set automatically when status becomes Published.'),
                ])->columns(2),

            Forms\Components\Section::make('Review & trust metadata')
                ->description('Required for provider-specific articles before they may be published (ADR-0004).')
                ->visible(fn (Get $get) => $get('knowledge_type') === 'provider')
                ->schema([
                    Forms\Components\DatePicker::make('last_reviewed_at')
                        ->native(false)
                        ->required(fn (Get $get) => $get('knowledge_type') === 'provider'),
                    Forms\Components\Select::make('reference_status')
                        ->options(ReferenceStatus::class)
                        ->native(false)
                        ->required(fn (Get $get) => $get('knowledge_type') === 'provider'),
                    Forms\Components\TextInput::make('reference_period')
                        ->maxLength(255)
                        ->placeholder('e.g. Allianz Basic Training compilation, August 2026')
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Sources')
                ->schema([
                    Forms\Components\Select::make('sources')
                        ->relationship('sources', 'title')
                        ->multiple()
                        ->preload()
                        ->native(false)
                        ->helperText('Attach the citation(s) this article draws on.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(50)->weight('medium'),
                Tables\Columns\IconColumn::make('provider_id')
                    ->label('Type')
                    ->tooltip(fn (Article $r) => $r->provider_id ? 'Provider-specific' : 'General knowledge')
                    ->icon(fn (Article $r) => $r->provider_id ? 'heroicon-o-building-office-2' : 'heroicon-o-globe-alt')
                    ->color(fn (Article $r) => $r->provider_id ? 'warning' : 'gray'),
                Tables\Columns\TextColumn::make('provider.name')->label('Provider')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('category.name')->label('Category')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('level')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('reference_status')->label('Ref. status')->badge()->placeholder('—'),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->toggleable(),
                Tables\Columns\TextColumn::make('last_reviewed_at')->date()->sortable()->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('published_at')->dateTime()->sortable()->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(ContentStatus::class),
                Tables\Filters\SelectFilter::make('level')->options(ContentLevel::class),
                Tables\Filters\SelectFilter::make('reference_status')->options(ReferenceStatus::class),
                Tables\Filters\SelectFilter::make('provider')->relationship('provider', 'name'),
                Tables\Filters\TernaryFilter::make('provider_id')
                    ->label('Knowledge type')
                    ->placeholder('All')
                    ->trueLabel('Provider-specific')
                    ->falseLabel('General knowledge')
                    ->queries(
                        true: fn ($q) => $q->whereNotNull('provider_id'),
                        false: fn ($q) => $q->whereNull('provider_id'),
                        blank: fn ($q) => $q,
                    ),
                Tables\Filters\Filter::make('needs_attention')
                    ->label('Needs review / historical')
                    ->query(fn ($q) => $q->whereIn('reference_status', [ReferenceStatus::NeedsReview->value, ReferenceStatus::Historical->value])),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }

    /**
     * Second line of defence behind the Article model's saving() guard: turn the
     * model's RuntimeException into a friendly Filament validation error.
     */
    public static function guardProviderPublish(array $data): array
    {
        $isProvider = ! empty($data['provider_id']);
        $isPublished = ($data['status'] ?? null) === ContentStatus::Published->value
            || ($data['status'] ?? null) === ContentStatus::Published;

        if ($isProvider && $isPublished && (empty($data['last_reviewed_at']) || empty($data['reference_status']))) {
            throw ValidationException::withMessages([
                'data.status' => 'Provider-specific articles need a last reviewed date and a reference status before they can be published.',
            ]);
        }

        return $data;
    }
}
