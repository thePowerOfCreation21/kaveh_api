<?php

namespace App\Actions;

use App\Exceptions\CustomException;
use App\Models\Article;
use App\Services\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ArticleAction extends Action
{
    protected array $validation_roles = [
        'get_query' => [
            'search' => 'string|max:100'
        ],
        'store' => [
            'title' => 'required|string|max:120',
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:10000',
            'content' => 'required|string|max:10000'
        ],
        'update' => [
            'title' => 'string|max:120',
            'image' => 'file|mimes:png,jpg,jpeg,gif|max:10000',
            'content' => 'string|max:10000'
        ]
    ];

    protected array $unusual_fields = [
        'image' => 'file'
    ];

    public function __construct()
    {
        $this->model = Article::class;
    }

    /**
     * @param Request $request
     * @param array|string $validation_role
     * @param callable|null $storing
     * @return mixed
     * @throws CustomException
     */
    public function store_by_request(Request $request, array|string $validation_role = 'store', callable $storing = null): mixed
    {
        return parent::store_by_request($request, $validation_role, $storing);
    }

    /**
     * @param Request $request
     * @param array|string $validation_role
     * @param array $query_addition
     * @param object|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return object
     * @throws CustomException
     */
    public function get_by_request(
        Request $request,
        array|string $validation_role = 'get_query',
        array $query_addition = ['limit_content' => true],
        object $eloquent = null,
        array $relations = [],
        array $order_by = ['id' => 'DESC']
    ): object
    {
        return parent::get_by_request($request, $validation_role, $query_addition, $eloquent, $relations, $order_by);
    }

    /**
     * @param array $query
     * @param object|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return object|null
     */
    public function query_to_eloquent(
        array $query,
        object $eloquent = null,
        array $relations = [],
        array $order_by = ['id' => 'DESC']
    ): object|null
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent, $relations, $order_by);

        if (isset($query['limit_content']) && $query['limit_content'])
        {
            $eloquent = $eloquent->selectRaw('
                articles.*,
                IF(
                    LENGTH(content) > 100,
                    CONCAT(SUBSTRING(content, 1, 100), "..."),
                    content
                ) AS content
            ');
        }

        if (isset($query['search']))
        {
            $eloquent = $eloquent->where('title', 'LIKE', "%{$query['search']}%");
        }

        return $eloquent;
    }

    /**
     * @param string $id
     * @param array $query
     * @param array $relations
     * @return mixed
     * @throws CustomException
     */
    public function get_by_id(string $id, array $query = [], array $relations = []): mixed
    {
        return parent::get_by_id($id, $query, $relations);
    }

    /**
     * @param string $id
     * @param array $query
     * @param callable|null $deleting
     * @return bool|int|null
     */
    public function delete_by_id(string $id, array $query = [], callable $deleting = null): bool|int|null
    {
        return parent::delete_by_id($id, $query, $deleting);
    }

    /**
     * @param object $eloquent
     * @param callable|null $deleting
     * @return mixed
     */
    public function delete_by_eloquent(object $eloquent, callable $deleting = null): mixed
    {
        if (is_null($deleting))
        {
            $deleting = function($eloquent)
            {
                foreach ($eloquent->get() AS $entity)
                {
                    if (is_file($entity->getAttribute('image')))
                    {
                        unlink($entity->getAttribute('image'));
                    }
                }
            };
        }

        return parent::delete_by_eloquent($eloquent, $deleting);
    }

    /**
     * @param object $eloquent
     * @param array $update_data
     * @param callable|null $updating
     * @return bool|int
     * @throws CustomException
     */
    public function update_by_eloquent(object $eloquent, array $update_data, callable $updating = null): bool|int
    {
        if (is_null($updating))
        {
            $updating = function($eloquent, $update_data)
            {
                $entity = $this->get_first_by_eloquent($eloquent);

                if (isset($update_data['image']))
                {
                    if (is_file($entity->getAttribute('image')))
                    {
                        unlink($entity->getAttribute('image'));
                    }
                }
            };
        }

        return parent::update_by_eloquent($eloquent, $update_data, $updating);
    }

    /**
     * @param Request $request
     * @param string $id
     * @param array|string $validation_role
     * @param callable|null $updating
     * @return int|bool
     * @throws CustomException
     */
    public function update_by_request_and_id (Request $request, string $id, array|string $validation_role = 'update', callable $updating = null): int|bool
    {
        return $this->update_by_request_and_query($request, ['id' => $id], $validation_role, $updating);
    }
}
