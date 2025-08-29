<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function __construct(
        private SearchService $searchService
    ) {}

    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $filters = [
            'category' => $request->get('category'),
            'status' => $request->get('status')
        ];

        $results = [];
        if (!empty($query)) {
            $results = $this->searchService->search($query, $filters);
        }

        $searchFilters = $this->searchService->getSearchFilters();

        return Inertia::render('Search/Index', [
            'results' => $results,
            'categories' => $searchFilters['categories'],
            'brands' => $searchFilters['brands'],
            'query' => $query,
            'filters' => $filters
        ]);
    }
}
