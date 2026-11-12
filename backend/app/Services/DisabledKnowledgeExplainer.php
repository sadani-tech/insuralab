<?php

namespace App\Services;

use App\Contracts\KnowledgeExplainer;

class DisabledKnowledgeExplainer implements KnowledgeExplainer
{
    public function available(): bool
    {
        return false;
    }

    public function explain(string $question, array $entries, array $passages): array
    {
        throw new \LogicException('No external AI provider is configured.');
    }
}
