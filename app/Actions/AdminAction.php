<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class AdminAction extends Action
{
    protected $validation_roles = [
        'get_query' => [
            'search' => 'string|max:100'
        ]
    ];

    public function __construct()
    {
        $this->model = Admin::class;
    }

    /**
     * converts query to laravel eloquent
     * filters by: (parent filters) + search
     *
     * @param array $query
     * @param null $eloquent
     * @return Model|Builder
     */
    public function query_to_eloquent(array $query, $eloquent = null)
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent);

        if (isset($query['search']))
        {
            $eloquent = $eloquent->where('user_name', 'LIKE', "%{$query['search']}%");
        }

        return $eloquent;
    }
}
