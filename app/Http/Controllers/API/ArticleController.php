<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Validator;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $cacheKey = 'articles_'.md5(serialize($request->query()));

        $articles = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($request) {
            $query = Article::query();

            if ($request->has('query')) {
                $query->where('title', 'like', '%'.$request->query('query').'%')
                    ->orWhere('body', 'like', '%'.$request->query('query').'%');
            }

            if ($request->has('author')) {
                $query->where('author', $request->query('author'));
            }

            return $query->orderBy('created_at', 'desc')->get();
        });

        return response()->json($articles, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Definisikan aturan validasi
            $rules = [
                'author' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($request) {
                        $title = $request->input('title');

                        // Periksa apakah ada artikel dengan author dan title yang sama
                        if (Article::where('author', $value)->where('title', $title)->exists()) {
                            $fail('The combination of author and title must be unique.');
                        }
                    },
                ],
                'title' => 'required|string|max:255',
                'body' => 'required|string',
            ];

            // Buat instance validator
            $validator = Validator::make($request->all(), $rules);

            // Periksa apakah validasi gagal
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Validasi berhasil, buat artikel baru
            $validatedData = $validator->validated();
            $article = Article::create($validatedData);

            // Sementara saya memakai hapus semua cache.
            Cache::flush();

            // Simpan artikel yang baru dibuat di cache dengan kunci unik
            $cacheKey = 'article_' . $article->id;
            Cache::put($cacheKey, $article, now()->addMinutes(10));

            return response()->json($article, 201);
        } catch (\Exception $e) {
            // Tangani pengecualian dan kembalikan respons JSON
            return response()->json([
                'message' => 'An error occurred while storing the article.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Membuat kunci cache unik untuk artikel berdasarkan ID
        $cacheKey = 'article_' . $id;

        // Mencoba mengambil artikel dari cache
        $article = Cache::get($cacheKey);

        // Jika artikel tidak ditemukan di cache, ambil dari database dan simpan di cache
        if (!$article) {
            $article = Article::findOrFail($id);
            Cache::put($cacheKey, $article, now()->addMinutes(10));
        }

        // Mengembalikan artikel sebagai respons JSON
        return response()->json($article);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            // Temukan artikel berdasarkan ID
            $article = Article::find($id);

            // Periksa apakah artikel ditemukan
            if (!$article) {
                return response()->json(['message' => 'Article not found.'], 404);
            }

            // Definisikan aturan validasi
            $rules = [
                'author' => 'required|string|max:255',
                'title' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($request, $article) {
                        // Periksa apakah ada artikel lain dengan author dan title yang sama
                        if (Article::where('author', $request->input('author'))
                                    ->where('title', $value)
                                    ->where('id', '!=', $article->id)
                                    ->exists()) {
                            $fail('The combination of author and title must be unique.');
                        }
                    },
                ],
                'body' => 'required|string',
            ];

            // Buat instance validator
            $validator = Validator::make($request->all(), $rules);

            // Periksa apakah validasi gagal
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Validasi berhasil, perbarui artikel
            $validatedData = $validator->validated();
            $article->update($validatedData);

            // Update cache dengan data artikel yang baru diperbarui
            $cacheKey = 'article_' . $article->id;
            Cache::put($cacheKey, $article, now()->addMinutes(10));

            return response()->json($article, 200);
        } catch (\Exception $e) {
            // Tangani pengecualian dan kembalikan respons JSON
            return response()->json([
                'message' => 'An error occurred while updating the article.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Temukan artikel berdasarkan ID
            $article = Article::find($id);

            // Periksa apakah artikel ditemukan
            if (!$article) {
                return response()->json(['message' => 'Article not found.'], 404);
            }

            // Hapus artikel dari database
            $article->delete();

            // Hapus artikel dari cache
            $cacheKey = 'article_' . $article->id;
            Cache::forget($cacheKey);

            return response()->json(['message' => 'Article deleted successfully'], 200);
        } catch (\Exception $e) {
            // Tangani pengecualian dan kembalikan respons JSON
            return response()->json([
                'message' => 'An error occurred while deleting the article.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
