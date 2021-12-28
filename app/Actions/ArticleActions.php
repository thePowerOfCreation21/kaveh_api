<?php

namespace App\Actions;

use Illuminate\Http\Request;
use App\Models\Article;
use function App\Helpers\UploadIt;

class ArticleActions
{
    /**
     * store new article in DB
     *
     * @param Request $request
     * @return Article
     */
    public static function store (Request $request): Article
    {
        $article_data = $request->validate([
            'title' => 'required|string|max:120',
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:2048',
            'content' => 'required|string|max:5000'
        ]);

        $article_data['image'] = UploadIt($_FILES['image'], ['png', 'jpg', 'jpeg', 'gif'], 'uploads/');

        return Article::create($article_data);
    }

    /**
     * get all Articles (has pagination)
     *
     * @param int $skip
     * @param int $limit
     * @return object
     */
    public static function get (int $skip = 0, int $limit = 50)
    {
        return (object) [
            'count' => Article::count(),
            'Articles' => Article::selectRaw('articles.*, IF(
                    LENGTH(content) > 100,
                    CONCAT(SUBSTRING(content, 1, 100), "..."),
                    content
                ) AS content')
                ->orderBy('id', 'DESC')
                ->skip($skip)
                ->take($limit)
                ->get()
        ];
    }
}
