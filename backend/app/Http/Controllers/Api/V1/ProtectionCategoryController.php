<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProtectionCategoryResource;
use App\Models\ProtectionCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProtectionCategoryController extends Controller
{
    /**
     * The protection concepts (life, health, critical illness, disability,
     * accident, income) used across Learn, Products, and the future Assessment.
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = ProtectionCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return ProtectionCategoryResource::collection($categories);
    }
}
