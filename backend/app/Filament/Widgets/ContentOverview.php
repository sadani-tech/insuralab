<?php

namespace App\Filament\Widgets;

use App\Enums\ContentStatus;
use App\Enums\ReferenceStatus;
use App\Models\Article;
use App\Models\GlossaryTerm;
use App\Models\Product;
use App\Models\Provider;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $needsReview = Article::where('reference_status', ReferenceStatus::NeedsReview->value)->count()
            + Product::where('reference_status', ReferenceStatus::NeedsReview->value)->count();

        $historical = Article::where('reference_status', ReferenceStatus::Historical->value)->count()
            + Product::where('reference_status', ReferenceStatus::Historical->value)->count();

        return [
            Stat::make('Published articles', Article::where('status', ContentStatus::Published->value)->count())
                ->description('General + provider-specific')
                ->color('success'),

            Stat::make('Draft articles', Article::where('status', ContentStatus::Draft->value)->count())
                ->color('gray'),

            Stat::make('Providers', Provider::count())
                ->description('Provider-agnostic — case studies only'),

            Stat::make('Products', Product::count())
                ->description('Educational records'),

            Stat::make('Glossary terms', GlossaryTerm::count()),

            Stat::make('Needs review', $needsReview)
                ->description('Articles + products awaiting verification')
                ->color($needsReview > 0 ? 'danger' : 'success'),

            Stat::make('Historical content', $historical)
                ->description('Kept for history — never shown as current')
                ->color($historical > 0 ? 'warning' : 'gray'),
        ];
    }
}
