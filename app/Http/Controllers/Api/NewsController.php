<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\Middleware;

class NewsController extends Controller
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum', except: [ 'index', 'show' ])
        ];
    }

    public function index(Request $request)
    {
        $news = News::query();

        if ($request->has('type')) {
            $news->where('type', $request->type);
        }

        if ($request->has('search')) {
            $news->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('article', 'LIKE', '%' . $request->search . '%');
            });
        }

        $news = $news->latest()->get();

        return response()->json([
            'success' => true, 
            'message' => 'News retrieved successfully', 
            'data' => $news
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image_path' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'article' => 'required|string',
            'author_name' => 'nullable|string|max:255',
            'type' => 'required|in:sport,health,politic,technology',
        ]);

        $imagePath = $request->file('image_path')->store('news_images', 'public');

        $news = News::create([
            'title' => $request->title,
            'image_path' => $imagePath,
            'article' => $request->article,
            'author_name' => $request->author_name,
            'type' => $request->type,
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'News created successfully', 
            'data' => $news
        ]);
    }

    public function show($id)
    {
        $news = News::findOrFail($id);

        return response()->json([
            'success' => true, 
            'message' => 'News retrieved successfully', 
            'data' => $news
        ]);
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'image_path' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'article' => 'sometimes|string',
            'author_name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:sport,health,politic,technology',
        ]);

        if ($request->hasFile('image_path')) {
            if ($news->image_path && Storage::disk('public')->exists($news->image_path)) {
                Storage::disk('public')->delete($news->image_path);
            }

            $imagePath = $request->file('image_path')->store('news_images', 'public');
            $news->image_path = $imagePath;
        }

        if ($request->has('title')) 
            $news->title = $request->title;
        
        if ($request->has('article')) 
            $news->article = $request->article;
        
        if ($request->has('author_name')) 
            $news->author_name = $request->author_name;
        
        if ($request->has('type')) 
            $news->type = $request->type;

        $news->save();

        return response()->json([
            'success' => true, 
            'message' => 'News updated successfully', 
            'data' => $news
        ]);
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);

        if ($news->image_path && Storage::disk('public')->exists($news->image_path)) {
            Storage::disk('public')->delete($news->image_path);
        }

        $news->delete();

        return response()->json([
            'success' => true, 
            'message' => 'News deleted successfully'
        ]);
    }
}
