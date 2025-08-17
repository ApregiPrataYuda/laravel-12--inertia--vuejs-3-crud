<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\TagsModel;

class TagsController extends Controller
{
    protected $TagsModel;
    public function __construct(TagsModel $TagsModel) {
        $this->TagsModel = $TagsModel;
    }


    function index(Request $request)  {
         $perPage = $request->input('per_page', 10); // default 10
         $search = $request->input('search');

        // default sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $tags = TagsModel::query()
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);

        return Inertia::render('Tags/Index', [
            'tags' => $tags,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_dir']),
        ]);

    //      $tags = TagsModel::query()
    //     ->when($request->search, fn($q) =>
    //         $q->where('name', 'like', "%{$request->search}%")
    //     )
    //     ->orderBy($request->sort_by ?? 'created_at', $request->sort_dir ?? 'desc')
    //     ->paginate($request->per_page ?? 10)
    //     ->appends($request->all());

    // return Inertia::render('Tags/Index', [
    //     'tags' => $tags,
    //     'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_dir']),
    // ]);
    }
}
