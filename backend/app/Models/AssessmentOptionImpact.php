<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class AssessmentOptionImpact extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_option_id', 'assessment_dimension_id', 'weight', 'reason_code', 'reason',
    ];

    protected $casts = ['weight' => 'integer'];

    protected static function booted(): void
    {
        static::saving(function (self $impact): void {
            if ($impact->weight < 0 || $impact->weight > 5) {
                throw ValidationException::withMessages([
                    'weight' => 'Bobot dampak harus berada pada rentang 0 sampai 5.',
                ]);
            }

            $optionAssessmentId = $impact->option?->question?->assessment_id;
            $dimensionAssessmentId = $impact->dimension?->assessment_id;
            if ($optionAssessmentId && $dimensionAssessmentId && $optionAssessmentId !== $dimensionAssessmentId) {
                throw ValidationException::withMessages([
                    'assessment_dimension_id' => 'Dimensi dan opsi harus berasal dari assessment yang sama.',
                ]);
            }
        });
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(AssessmentOption::class, 'assessment_option_id');
    }

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(AssessmentDimension::class, 'assessment_dimension_id');
    }
}
