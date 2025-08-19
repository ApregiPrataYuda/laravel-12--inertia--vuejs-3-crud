<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Models\TagsModel;
use App\Http\Requests\TagsValidation;
use Illuminate\Validation\ValidationException;

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
    }





        public function store(TagsValidation $request)
            {
                try {
                    if (TagsModel::isTagsExists($request->input('name'))) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'name' => 'Tags name already exists.'
                        ]);
                    }

                    $tag = $this->TagsModel->create([
                        'name' => $request->name,
                        'slug' => Str::slug($request->name)
                    ]);

                    return response()->json([
                        'message' => 'Success save',
                        'data' => $tag
                    ], 201);

                } catch (\Illuminate\Validation\ValidationException $e) {
                    throw $e; // biarkan Laravel handle validasi → otomatis kirim JSON errors
                } catch (\Throwable $th) {
                    return response()->json([
                        'message' => 'Failed to create data. Please try again.'
                    ], 500);
                }
            }



public function update(TagsValidation $request, $id)
{
    try {
        $tag = $this->TagsModel->findOrFail($id);

        if (TagsModel::isTagsExistsEdit($request->input('name'), $id)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => 'Tags name already exists.'
            ]);
        }

        $tag->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json([
            'message' => 'Success update',
            'data' => $tag
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        throw $e;
    } catch (\Throwable $th) {
        return response()->json([
            'message' => 'Failed to update data. Please try again.'
        ], 500);
    }
}




        public function destroy($id)  {




    $TagsData = TagsModel::find($id);

    if (!$TagsData) {
        return response()->json([
            'message' => 'Data ID Not Found!'
        ], 404);
    }

    $TagsData->delete();

    return response()->json([
        'message' => 'Success delete'
    ], 200);

}
}
