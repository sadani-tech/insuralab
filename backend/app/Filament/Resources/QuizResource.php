<?php

namespace App\Filament\Resources;

use App\Enums\ContentLevel;
use App\Enums\ContentStatus;
use App\Filament\Resources\QuizResource\Pages;
use App\Models\Quiz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Learning';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set, ?Quiz $record) => $record ? null : $set('slug', \Illuminate\Support\Str::slug((string) $state))),
                Forms\Components\TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                Forms\Components\Select::make('category_id')->relationship('category', 'name')->searchable()->preload()->native(false),
                Forms\Components\Select::make('level')->options(ContentLevel::class)->default(ContentLevel::Beginner)->required()->native(false),
                Forms\Components\TextInput::make('estimated_duration_minutes')->label('Estimated duration')->numeric()->minValue(1)->suffix('minutes'),
                Forms\Components\Select::make('status')->options(ContentStatus::class)->default(ContentStatus::Draft)->required()->native(false),
                Forms\Components\Textarea::make('description')->rows(2)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Repeater::make('questions')
                ->relationship()
                ->orderColumn('sort_order')
                ->itemLabel(fn (array $state): ?string => $state['prompt'] ?? null)
                ->collapsible()
                ->schema([
                    Forms\Components\TextInput::make('key')->required()->alphaDash()->maxLength(100)
                        ->helperText('Stable semantic key used by the public submission API.'),
                    Forms\Components\Textarea::make('prompt')->required()->rows(2),
                    Forms\Components\Textarea::make('explanation')->required()->rows(2)->helperText('Concept explanation returned after submission.'),
                    Forms\Components\Hidden::make('type')->default('single'),
                    Forms\Components\Select::make('relatedArticles')
                        ->label('Related general lessons')
                        ->relationship('relatedArticles', 'title', modifyQueryUsing: fn ($query) => $query->general()->public())
                        ->multiple()->searchable()->preload()->columnSpanFull()
                        ->helperText('Used for deterministic review recommendations after a missed answer.'),
                    Forms\Components\Repeater::make('options')
                        ->relationship()
                        ->orderColumn('sort_order')
                        ->minItems(2)
                        ->schema([
                            Forms\Components\TextInput::make('key')->required()->alphaDash()->maxLength(100),
                            Forms\Components\TextInput::make('label')->required()->columnSpan(2),
                            Forms\Components\Toggle::make('is_correct')->inline(false),
                            Forms\Components\Textarea::make('explanation')->required()->rows(2)->columnSpanFull()
                                ->helperText('Always explain the selected option without shaming the learner. Exactly one option must be correct.'),
                        ])->columns(4)
                        ->defaultItems(3),
                ])
                ->defaultItems(1)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->weight('medium'),
                Tables\Columns\TextColumn::make('category.name')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('level')->badge(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('questions_count')->counts('questions')->label('Questions'),
                Tables\Columns\TextColumn::make('estimated_duration_minutes')->suffix(' min')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(ContentStatus::class),
                Tables\Filters\SelectFilter::make('level')->options(ContentLevel::class),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }
}
