<?php

namespace App\Filament\Resources;

use App\Enums\ProviderStatus;
use App\Filament\Resources\ProviderResource\Pages;
use App\Models\Provider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProviderResource extends Resource
{
    protected static ?string $model = Provider::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Providers & Products';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Forms\Set $set, ?Provider $record) => $record ? null : $set('slug', \Illuminate\Support\Str::slug((string) $state))),
                    Forms\Components\TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                    Forms\Components\Textarea::make('description')->rows(6)->columnSpanFull()
                        ->helperText('This provider is a case study, not the platform identity. Keep entity/distribution distinctions explicit.'),
                    Forms\Components\TextInput::make('country_of_origin')->maxLength(255),
                    Forms\Components\TextInput::make('website_url')->url()->maxLength(255),
                    Forms\Components\Textarea::make('presence_in_indonesia')->rows(3)->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Status & review')
                ->schema([
                    Forms\Components\Select::make('status')->options(ProviderStatus::class)->default(ProviderStatus::Active)->required()->native(false),
                    Forms\Components\DatePicker::make('last_reviewed_at')->native(false),
                    Forms\Components\Select::make('sources')->relationship('sources', 'title')->multiple()->preload()->native(false)->columnSpanFull(),
                    Forms\Components\KeyValue::make('metadata')->label('Controlled metadata (optional)')->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->weight('medium'),
                Tables\Columns\TextColumn::make('country_of_origin')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('products_count')->counts('products')->label('Products'),
                Tables\Columns\TextColumn::make('articles_count')->counts('articles')->label('Articles'),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('last_reviewed_at')->date()->sortable()->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(ProviderStatus::class),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProviders::route('/'),
            'create' => Pages\CreateProvider::route('/create'),
            'edit' => Pages\EditProvider::route('/{record}/edit'),
        ];
    }
}
