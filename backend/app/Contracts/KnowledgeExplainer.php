<?php

namespace App\Contracts;

/** A future provider receives only public passages already selected by the server. */
interface KnowledgeExplainer
{
    public function available(): bool;

    /** @return array{text: string, passage_ids: array<int>} */
    public function explain(string $question, array $entries, array $passages): array;
}
