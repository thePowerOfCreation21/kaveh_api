<?php

namespace App\Actions;

use App\Exceptions\CustomException;
use App\Services\PaginationService;
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
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:10000',
            'content' => 'required|string|max:5000'
        ]);

        $article_data['image'] = $request->file('image')->store('/uploads');

        return Article::create($article_data);
    }

    /**
     * get articles with request
     * (uses PaginationService to paginate)
     *
     * @param Request $request
     * @return object
     */
    public static function get_with_request (Request $request)
    {
        return PaginationService::paginate_with_request(
            $request,
            Article::selectRaw('
                articles.*,
                IF(
                    LENGTH(content) > 100,
                    CONCAT(SUBSTRING(content, 1, 100), "..."),
                    content
                ) AS content
            ')
            ->orderBy('id', 'DESC')
        );
    }

    /**
     * get article by id (returns 404 http response if id is wrong then dies)
     *
     * @param string $id
     * @return Article
     * @throws CustomException
     */
    public static function get_by_id (string $id): Article
    {
        $article = Article::where('id', $id)->first();

        if (empty($article))
        {
            throw new CustomException("couldn't find article with this id", 15, 404);
        }

        return $article;
    }

    /**
     * delete article by id
     * returns 404 http response if id is wrong then dies
     * removes the image file
     *
     * @param string $id
     * @return int
     * @throws CustomException
     */
    public static function delete_by_id (string $id): int
    {
        $article = self::get_by_id($id);

        if (is_file($article->image))
        {
            unlink($article->image);
        }

        return $article->delete();
    }

    /**
     * update article by id
     * returns 404 http response if id is wrong then dies
     * only changes the given field(s)
     * removes the old image file
     *
     * @param Request $request
     * @param string $id
     * @return bool
     * @throws CustomException
     */
    public static function update_by_id (Request $request, string $id): bool
    {
        $request->validate([
            'title' => 'string|max:120',
            'image' => 'file|mimes:png,jpg,jpeg,gif|max:2048',
            'content' => 'string|max:5000'
        ]);

        $article = self::get_by_id($id);

        $update = [];

        !empty($request->input('title')) && $update['title'] = $request->input('title');
        !empty($request->input('content')) && $update['content'] = $request->input('content');

        if (! empty($request->file('image')))
        {
            if (is_file($article->image))
            {
                unlink($article->image);
            }
            $update['image'] = $request->file('image')->store('/uploads');
        }

        return $article->update($update);
    }
}
