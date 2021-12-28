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

        $article_data['iamge'] = UploadIt($_FILES['image'], ['png', 'jpg', 'jpeg', 'gif'], 'uploads/');

        return Article::create($article_data);
    }
}
