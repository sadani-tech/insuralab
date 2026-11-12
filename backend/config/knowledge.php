<?php

return [
    // A real adapter must be explicitly bound before optional AI is available.
    'ai_enabled' => (bool) env('KNOWLEDGE_AI_ENABLED', false),
];
