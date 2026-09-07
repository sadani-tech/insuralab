<?php

namespace App\Filament\Resources;

use App\Enums\ContentStatus;
use App\Filament\Resources\AssessmentResource\Pages;
use App\Models\Assessment;
use App\Models\AssessmentDimension;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssessmentResource extends Resource
{
    protected static ?string $model = Assessment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Learning';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Assessment')
                ->description('Atur metodologi relevansi pembelajaran. Hasil hanya boleh mengarah ke materi umum, bukan provider atau produk.')
                ->schema([
                    Forms\Components\TextInput::make('title')->required()->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Forms\Set $set, ?Assessment $record) => $record ? null : $set('slug', \Illuminate\Support\Str::slug((string) $state))),
                    Forms\Components\TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                    Forms\Components\Select::make('status')->options(ContentStatus::class)->default(ContentStatus::Draft)->required()->native(false),
                    Forms\Components\TextInput::make('methodology_version')->required()->default('1.0')->maxLength(30)
                        ->helperText('Naikkan versi ketika pertanyaan, bobot, atau ambang metodologi berubah.'),
                    Forms\Components\Textarea::make('description')->rows(2)->columnSpanFull(),
                    Forms\Components\Textarea::make('intro')->rows(3)->columnSpanFull(),
                    Forms\Components\Textarea::make('disclaimer')->rows(2)->columnSpanFull()
                        ->default('Assessment ini bersifat edukatif dan bukan rekomendasi untuk membeli produk tertentu.'),
                ])->columns(2),

            Forms\Components\Section::make('Ambang relevansi')
                ->description('Nilai normalisasi 0–1. Ambang “Sangat Relevan” harus lebih tinggi daripada “Layak Dieksplorasi”.')
                ->schema([
                    Forms\Components\TextInput::make('rules.thresholds.worth_exploring')
                        ->label('Layak Dieksplorasi mulai')->numeric()->minValue(0)->maxValue(1)->step(0.01)->default(0.30)->required(),
                    Forms\Components\TextInput::make('rules.thresholds.highly_relevant')
                        ->label('Sangat Relevan mulai')->numeric()->minValue(0)->maxValue(1)->step(0.01)->default(0.60)->required()
                        ->gt('rules.thresholds.worth_exploring'),
                ])->columns(2),

            Forms\Components\Repeater::make('dimensions')
                ->relationship()->orderColumn('sort_order')->collapsible()->minItems(1)
                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                ->schema([
                    Forms\Components\TextInput::make('key')->required()->alphaDash()->maxLength(100),
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\Textarea::make('description')->required()->rows(2)->columnSpanFull(),
                    Forms\Components\Select::make('learning_path_id')
                        ->label('Jalur belajar terkait')
                        ->relationship('learningPath', 'title', modifyQueryUsing: fn ($query) => $query->published())
                        ->searchable()->preload()
                        ->helperText('Jalur general yang dapat ditawarkan setelah assessment.'),
                    Forms\Components\Select::make('learningArticles')
                        ->label('Materi belajar umum')
                        ->relationship(
                            name: 'learningArticles',
                            titleAttribute: 'title',
                            modifyQueryUsing: fn ($query) => $query->general()->public(),
                        )
                        ->multiple()->preload()->searchable()->columnSpanFull()
                        ->helperText('Hanya artikel general yang dapat muncul pada hasil assessment.'),
                ])->columns(2)->columnSpanFull(),

            Forms\Components\Repeater::make('questions')
                ->relationship()->orderColumn('sort_order')->collapsible()->defaultItems(1)
                ->itemLabel(fn (array $state): ?string => $state['prompt'] ?? null)
                ->schema([
                    Forms\Components\TextInput::make('key')->required()->alphaDash()->maxLength(100)
                        ->helperText('Kunci semantik stabil dan unik dalam assessment.'),
                    Forms\Components\Textarea::make('prompt')->required()->rows(2),
                    Forms\Components\TextInput::make('help_text')->columnSpanFull(),
                    Forms\Components\Hidden::make('type')->default('single'),
                    Forms\Components\Toggle::make('is_required')->default(true),
                    Forms\Components\TextInput::make('depends_on_question_key')
                        ->label('Berlaku jika pertanyaan')->helperText('Kosongkan jika selalu berlaku.'),
                    Forms\Components\TagsInput::make('applicable_option_keys')
                        ->label('Memiliki pilihan')->helperText('Daftar option key pada pertanyaan prasyarat.'),
                    Forms\Components\Repeater::make('options')
                        ->relationship()->orderColumn('sort_order')->minItems(2)->defaultItems(3)
                        ->schema([
                            Forms\Components\TextInput::make('key')->required()->alphaDash()->maxLength(100),
                            Forms\Components\TextInput::make('label')->required(),
                            Forms\Components\Repeater::make('impacts')
                                ->relationship()->maxItems(5)->columnSpanFull()
                                ->schema([
                                    Forms\Components\Select::make('assessment_dimension_id')
                                        ->label('Dimensi')
                                        ->options(fn () => AssessmentDimension::query()->with('assessment:id,title')->get()
                                            ->mapWithKeys(fn ($dimension) => [$dimension->id => $dimension->assessment->title.' — '.$dimension->name]))
                                        ->required()->searchable()->preload(),
                                    Forms\Components\TextInput::make('weight')->numeric()->minValue(0)->maxValue(5)->required(),
                                    Forms\Components\TextInput::make('reason_code')->alphaDash()->maxLength(100),
                                    Forms\Components\Textarea::make('reason')->rows(2)->columnSpanFull()
                                        ->helperText('Alasan editorial Bahasa Indonesia yang dapat ditampilkan kepada pengguna.'),
                                ])->columns(2),
                            Forms\Components\Select::make('learningArticles')
                                ->label('Materi tambahan untuk pilihan ini')
                                ->relationship(
                                    name: 'learningArticles',
                                    titleAttribute: 'title',
                                    modifyQueryUsing: fn ($query) => $query->general()->public(),
                                )
                                ->multiple()->preload()->searchable()->columnSpanFull(),
                        ])->columns(2)->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->weight('medium'),
                Tables\Columns\TextColumn::make('methodology_version')->label('Version')->badge(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('questions_count')->counts('questions')->label('Questions'),
                Tables\Columns\TextColumn::make('dimensions_count')->counts('dimensions')->label('Dimensions'),
            ])
            ->filters([Tables\Filters\SelectFilter::make('status')->options(ContentStatus::class)])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssessments::route('/'),
            'create' => Pages\CreateAssessment::route('/create'),
            'edit' => Pages\EditAssessment::route('/{record}/edit'),
        ];
    }
}
