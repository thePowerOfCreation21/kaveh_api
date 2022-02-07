<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Models\Article;

class ArticleAction extends Action
{
    public function __construct()
    {
        $this->model = Article::class;
    }
}
