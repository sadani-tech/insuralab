<?php

namespace App\Filament\Resources;

use App\Enums\ContentStatus;
use App\Enums\InsuranceScheme;
use App\Enums\ProductStructure;
use App\Enums\ReferenceStatus;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Providers & Products';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identity')
                ->schema([
                    Forms\Components\Select::make('provider_id')
                        ->relationship('provider', 'name')
                        ->required()->searchable()->preload()->native(false),
                    Forms\Components\Select::make('product_category_id')
                        ->relationship('productCategory', 'name')
                        ->required()->searchable()->preload()->native(false),
                    Forms\Components\TextInput::make('name')
                        ->required()->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Forms\Set $set, ?Product $record) => $record ? null : $set('slug', \Illuminate\Support\Str::slug((string) $state))),
                    Forms\Components\TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                    Forms\Components\Select::make('insurance_scheme')->options(InsuranceScheme::class)->default(InsuranceScheme::Conventional)->required()->native(false),
                    Forms\Components\Select::make('product_structure')->options(ProductStructure::class)->default(ProductStructure::Other)->required()->native(false),
                    Forms\Components\Select::make('protectionCategories')
                        ->label('Protection concepts')
                        ->relationship('protectionCategories', 'name')
                        ->multiple()->preload()->native(false)
                        ->helperText('Which protection concepts this product responds to (life, health, critical illness, disability, accident, income).')
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Description')
                ->schema([
                    Forms\Components\Textarea::make('short_description')->rows(2)->maxLength(500)->columnSpanFull(),
                    Forms\Components\Textarea::make('description')->rows(8)->columnSpanFull()
                        ->helperText('Markdown. Do not fabricate current product facts — describe what the training reference says and its status.'),
                ]),

            Forms\Components\Section::make('Review & trust metadata')
                ->description('Provider/product data is versioned and source-aware (ADR-0004). Keep these current.')
                ->icon('heroicon-o-shield-check')
                ->schema([
                    Forms\Components\Select::make('reference_status')
                        ->options(ReferenceStatus::class)
                        ->default(ReferenceStatus::NeedsReview)
                        ->required()->native(false)
                        ->helperText('current = re-verified · historical = past/superseded · needs_review = unverified or source inconsistent'),
                    Forms\Components\DatePicker::make('last_reviewed_at')->native(false)
                        ->helperText('When an editor last checked this against an official document.'),
                    Forms\Components\TextInput::make('reference_period')
                        ->maxLength(255)
                        ->placeholder('e.g. Allianz Basic Training compilation, August 2026')
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Publication & sources')
                ->schema([
                    Forms\Components\Select::make('status')->options(ContentStatus::class)->default(ContentStatus::Draft)->required()->native(false),
                    Forms\Components\Select::make('sources')
                        ->relationship('sources', 'title')
                        ->multiple()->preload()->native(false),
                    Forms\Components\KeyValue::make('metadata')
                        ->label('Controlled metadata (optional)')
                        ->helperText('Optional, extensible attributes only. Anything filterable belongs in a real column.')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->weight('medium')->limit(40),
                Tables\Columns\TextColumn::make('provider.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('productCategory.name')->label('Category')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('insurance_scheme')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('product_structure')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('reference_status')->label('Ref. status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('last_reviewed_at')->date()->sortable()->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider')->relationship('provider', 'name'),
                Tables\Filters\SelectFilter::make('reference_status')->options(ReferenceStatus::class),
                Tables\Filters\SelectFilter::make('status')->options(ContentStatus::class),
                Tables\Filters\SelectFilter::make('insurance_scheme')->options(InsuranceScheme::class),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
