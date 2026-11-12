<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\KnowledgeLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    public function assist(Request $request, \App\Services\KnowledgeAnswers $answers): JsonResponse
    {
        $input = $request->validate(['question' => ['required', 'string', 'max:500'], 'use_ai' => ['sometimes', 'boolean']]);

        return response()->json(['data' => $answers->answer($input['question'], (bool) ($input['use_ai'] ?? false))])
            ->header('Cache-Control', 'no-store');
    }

    public function index(Request $request, KnowledgeLibrary $library): JsonResponse
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:160']]);

        // No shared cache: publication and access revocations take effect immediately.
        return response()->json(['data' => $library->snapshot(trim($validated['q'] ?? ''))])
            ->header('Cache-Control', 'no-store');
    }
}
