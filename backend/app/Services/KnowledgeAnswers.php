<?php

namespace App\Services;

use App\Contracts\KnowledgeExplainer;

class KnowledgeAnswers
{
    public function __construct(private KnowledgeLibrary $library, private KnowledgeExplainer $explainer) {}

    public function answer(string $question, bool $useAi): array
    {
        $library = $this->library->snapshot();
        $words = preg_split('/[^\p{L}\p{N}-]+/u', mb_strtolower($question), -1, PREG_SPLIT_NO_EMPTY);
        $words = array_diff($words, ['apa', 'apakah', 'itu', 'yang', 'dan', 'di', 'untuk', 'saya', 'bagaimana', 'jelaskan']);
        $entries = collect($library['entries'])->map(function ($entry) use ($words) {
            $text = mb_strtolower($entry['title'].' '.$entry['summary'].' '.implode(' ', $entry['keywords']));
            $entry['_score'] = count(array_filter($words, fn ($word) => mb_strlen($word) > 2 && str_contains($text, $word)));

            return $entry;
        })->filter(fn ($e) => $e['_score'] > 0)->sortByDesc('_score')->take(5)->map(function ($e) {
            unset($e['_score']);

            return $e;
        })->values()->all();
        $passageIds = array_column($entries, 'passage_id');
        $passages = collect($library['passages'])->whereIn('id', $passageIds)->values()->all();
        $result = ['mode' => 'curated', 'entries' => $entries, 'passages' => $passages, 'explanation' => null];
        if (! $useAi || ! config('knowledge.ai_enabled') || ! $this->explainer->available() || $entries === []) {
            return $result;
        }
        try {
            $answer = $this->explainer->explain($question, $entries, $passages);
            if (! is_string($answer['text'] ?? null) || trim($answer['text']) === '' || empty($answer['passage_ids']) || array_diff($answer['passage_ids'], $passageIds)) {
                return $result;
            }

            return [...$result, 'mode' => 'ai', 'explanation' => $answer];
        } catch (\Throwable) {
            // Provider outages must never remove the editorial search results.
            return $result;
        }
    }
}
