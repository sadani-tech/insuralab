<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GlossaryTermResource\Pages;
use App\Models\GlossaryTerm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GlossaryTermResource extends Resource
{
    protected static ?string $model = GlossaryTerm::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Knowledge';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('explorer_published')->label('Tampil di explorer')->default(false),
                Forms\Components\Select::make('metadata.explorer_group')->options(['dasar' => 'Dasar perlindungan', 'polis' => 'Mengenal polis', 'proteksi' => 'Jenis proteksi', 'klaim' => 'Klaim dan layanan', 'proses' => 'Proses', 'syariah' => 'Syariah']),
                Forms\Components\Textarea::make('metadata.explorer_summary')->label('Penjelasan pengantar explorer')->columnSpanFull(),
                Forms\Components\TextInput::make('term')
                    ->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set, ?GlossaryTerm $record) => $record ? null : $set('slug', \Illuminate\Support\Str::slug((string) $state))),
                Forms\Components\TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                Forms\Components\Select::make('category')
                    ->options([
                        'general' => 'General',
                        'sharia' => 'Sharia',
                        'product' => 'Product',
                        'process' => 'Process',
                        'compliance' => 'Compliance',
                        'agency' => 'Agency',
                    ])
                    ->default('general')->required()->native(false),
                Forms\Components\Select::make('provider_id')
                    ->relationship('provider', 'name')->searchable()->preload()->native(false)
                    ->helperText('Leave empty for a general term. Set only for provider-specific vocabulary.'),
                Forms\Components\Textarea::make('short_definition')->required()->rows(2)->maxLength(400)
                    ->helperText('One tooltip-length sentence, no jargon.')->columnSpanFull(),
                Forms\Components\Textarea::make('definition')->required()->rows(5)->columnSpanFull(),
                Forms\Components\Select::make('relatedTerms')
                    ->relationship('relatedTerms', 'term')
                    ->multiple()->preload()->native(false)->columnSpanFull(),
                Forms\Components\Select::make('reference_status')
                    ->options(\App\Enums\ReferenceStatus::class)->native(false),
                Forms\Components\DatePicker::make('last_reviewed_at')->native(false),
                Forms\Components\Select::make('sources')
                    ->relationship('sources', 'title')->multiple()->preload()->native(false)->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('term')->searchable()->weight('medium'),
                Tables\Columns\TextColumn::make('category')->badge()->sortable(),
                Tables\Columns\TextColumn::make('provider.name')->label('Provider')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('short_definition')->limit(70)->wrap()->toggleable(),
                Tables\Columns\TextColumn::make('reference_status')->badge()->placeholder('—'),
                Tables\Columns\TextColumn::make('last_reviewed_at')->date()->sortable()->placeholder('—')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->options([
                    'general' => 'General', 'sharia' => 'Sharia', 'product' => 'Product',
                    'process' => 'Process', 'compliance' => 'Compliance', 'agency' => 'Agency',
                ]),
                Tables\Filters\SelectFilter::make('provider')->relationship('provider', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGlossaryTerms::route('/'),
            'create' => Pages\CreateGlossaryTerm::route('/create'),
            'edit' => Pages\EditGlossaryTerm::route('/{record}/edit'),
        ];
    }
}
