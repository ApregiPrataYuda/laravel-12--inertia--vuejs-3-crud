<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoryModel;
use Inertia\Inertia;
class CategoryController extends Controller
{
    protected $CategoryModel;
    public function __construct(CategoryModel $CategoryModel) {
        $this->CategoryModel = $CategoryModel;
    }

//  function index(Request $request)  {
//         //    $perPage = $request->input('per_page', 10);
//         //     $categories = CategoryModel::query()
//         //     ->when($request->search, function ($query, $search) {
//         //         $query->where('name', 'like', "%{$search}%");
//         //     })
//         //     ->orderBy('created_at', 'desc')
//         //     ->paginate(10)
//         //     ->withQueryString();

//         // return Inertia::render('Data/Category', [
//         //     'category' => $categories,
//         //     'filters' => $request->only('search'),
//         // ]);

        
//         $perPage = $request->input('per_page', 10); // default 10
//         $search = $request->input('search');

//         $category = CategoryModel::query()
//             ->when($search, function ($q) use ($search) {
//                 $q->where('name', 'like', "%{$search}%");
//             })
//             ->orderBy('created_at', 'desc')
//             ->paginate($perPage);

//         return Inertia::render('Data/Category', [
//             'category' => $category,
//             'filters' => $request->only(['search', 'per_page']),
//         ]);
//     }
    


public function index(Request $request)
{
    $perPage = $request->input('per_page', 10); // default 10
    $search = $request->input('search');

    // default sorting
    $sortBy = $request->input('sort_by', 'created_at');
    $sortDir = $request->input('sort_dir', 'desc');

    $category = CategoryModel::query()
        ->when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        })
        ->orderBy($sortBy, $sortDir)
        ->paginate($perPage);

    return Inertia::render('Data/Category', [
        'category' => $category,
        'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_dir']),
    ]);
}

    
}
