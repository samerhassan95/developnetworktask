<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Container\Attributes\Log;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as FacadesLog;

class PostController extends Controller
{
    public function index()
    {
        return Post::where('user_id', Auth::id())->orderBy('pinned', 'desc')->get();
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'body' => 'required|string',
                'cover_image' => 'required|image',
                'pinned' => 'required|boolean',
                'tags' => 'required|array',
            ]);

            $post = Auth::user()->posts()->create($request->only(['title', 'body', 'cover_image', 'pinned']));
            $post->tags()->attach($request->tags);

            return response()->json($post, 201);
        } catch (QueryException $e) {
            FacadesLog::error('Database Error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Database error occurred'], 500);
        } catch (\Exception $e) {
            FacadesLog::error('General Error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An error occurred'], 500);
        }
    }


    public function show($id)
    {
        // Find the post by ID
        $post = Post::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // Authorize the request
        $this->authorize('view', $post);

        // Return the post
        return response()->json($post);
    }

    public function update(Request $request, Post $post)
    {
        // Log the incoming request data
        FacadesLog::info('Request Data', ['data' => $request->all()]);
        FacadesLog::info('File Data', ['file' => $request->file('cover_image')]);

        // Check if the request contains form data or JSON
        $isFormData = $request->is('multipart/form-data');

        // Validate incoming data
        $validatedData = $request->validate([
            'title' => 'sometimes|string|max:255',
            'body' => 'sometimes|string',
            'cover_image' => 'sometimes|image',
            'pinned' => 'sometimes|boolean',
            'tags' => 'sometimes|array',
        ]);

        // Update the post with validated data
        $post->update($validatedData);

        // Handle cover image file if present
        if ($request->hasFile('cover_image')) {
            // Store the file and get the path
            $coverImagePath = $request->file('cover_image')->store('public/cover_images');
            // Update the post with the new cover image path
            $post->cover_image = $coverImagePath;
            $post->save(); // Save the post again to persist cover_image change
        }

        // Handle tags if present
        if ($request->has('tags')) {
            $tagIds = [];
            foreach ($request->tags as $tagName) {
                $tag = Tag::where('name', $tagName)->first();
                if ($tag) {
                    $tagIds[] = $tag->id;
                } else {
                    return response()->json(['error' => 'Tag not found: ' . $tagName], 422);
                }
            }
            $post->tags()->sync($tagIds);
        }

        return response()->json($post, 200);
    }








    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        $post->delete();
        return response()->json(['message' => 'Post softly deleted']);
    }

    public function trashed()
    {
        return Post::onlyTrashed()->where('user_id', Auth::id())->get();
    }

    public function restore($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $post);
        $post->restore();
        return response()->json(['message' => 'Post restored']);
    }
}
