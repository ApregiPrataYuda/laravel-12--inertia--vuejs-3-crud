<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Models\TagsModel;
use App\Http\Requests\TagsValidation;
use Illuminate\Validation\ValidationException;

class TagController extends Controller
{
     protected $TagsModel;

    public function __construct(TagsModel $TagsModel) {
        $this->TagsModel = $TagsModel;
    }

    // Render page pakai Inertia
    public function index()
    {
        return Inertia::render('Tag/Index');
    }

    // JSON data untuk axios
    public function getData(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');
        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $tags = TagsModel::query()
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);

        return response()->json($tags);
    }

    public function store(TagsValidation $request)
    {
        if (TagsModel::isTagsExists($request->input('name'))) {
            throw ValidationException::withMessages([
                'name' => 'Tags name already exists.'
            ]);
        }

        $tag = $this->TagsModel->create([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return response()->json([
            'message' => 'Success save',
            'data'    => $tag
        ], 201);
    }

    public function update(TagsValidation $request, $id)
    {
        $tag = $this->TagsModel->findOrFail($id);

        if (TagsModel::isTagsExistsEdit($request->input('name'), $id)) {
            throw ValidationException::withMessages([
                'name' => 'Tags name already exists.'
            ]);
        }

        $tag->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json([
            'message' => 'Success update',
            'data'    => $tag
        ]);
    }

    public function destroy($id)
    {
        $tag = TagsModel::find($id);

        if (!$tag) {
            return response()->json([
                'message' => 'Data ID Not Found!'
            ], 404);
        }

        $tag->delete();

        return response()->json([
            'message' => 'Success delete'
        ]);
    }


    // method deleteMany
    public function deleteMany(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:tags,id',
        ]);

        $ids = $request->input('ids');

        try {
            \DB::transaction(function () use ($ids) {
                \App\Models\TagsModel::whereIn('id', $ids)->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Tags berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
