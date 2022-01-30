<?php

namespace App\Actions;

use App\Exceptions\CustomException;
use App\Services\PaginationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use App\Models\CommonQuestion;

class CommonQuestionActions
{
    /**
     * store CommonQuestion from Request
     *
     * @param Request $request
     * @return CommonQuestion
     */
    public static function store_from_request (Request $request): CommonQuestion
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:1500',
        ]);

        return CommonQuestion::create($data);
    }

    /**
     * get common questions
     * (has pagination (get values from request))
     *
     * @param Request $request
     * @return object
     */
    public static function get_by_request (Request $request)
    {
        $pagination_values = PaginationService::get_values_from_request($request);

        return self::get($pagination_values['skip'], $pagination_values['limit']);
    }

    /**
     * get common questions
     *
     * @param int $skip
     * @param int $limit
     * @return object
     */
    public static function get (int $skip = 0, int $limit = 50)
    {
        return PaginationService::paginate(
            (new CommonQuestion())->orderBy('id', 'DESC'),
            $skip, $limit
        );
    }

    /**
     * get CommonQuestion by id
     *
     * @param string $id
     * @return CommonQuestion
     * @throws CustomException
     */
    public static function get_by_id (string $id): CommonQuestion
    {
        $commonQuestion = CommonQuestion::where('id', $id)->first();

        if (empty($commonQuestion))
        {
            throw new CustomException("CommonQuestion with id '{$id}' not found", 60, 404);
        }

        return $commonQuestion;
    }

    /**
     * update CommonQuestion by id
     * gets values from Request
     *
     * @param Request $request
     * @param string $id
     * @return bool|int
     * @throws CustomException
     */
    public static function edit_by_request_and_id (Request $request, string $id)
    {
        $data = $request->validate([
            'question' => 'string|max:255',
            'answer' => 'string|max:1500'
        ]);

        return self::edit($data, ['id' => $id]);
    }

    /**
     * update CommonQuestion(s)
     *
     * @param array $data
     * @param array $query
     * @return bool|int
     * @throws CustomException
     */
    public static function edit (array $data, array $query)
    {
        $eloquent = self::query_to_eloquent($query);

        if (! $eloquent->exists())
        {
            throw new CustomException('common question not found', 62, 404);
        }

        return $eloquent->update($data);
    }

    /**
     * delete CommonQuestion by id
     *
     * @param string $id
     * @return bool|int|null
     */
    public static function delete_by_id (string $id)
    {
        return self::delete(['id' => $id]);
    }

    /**
     * delete CommonQuestion(s)
     *
     * @param array $query
     * @return bool|int|null
     */
    public static function delete (array $query)
    {
        $eloquent = self::query_to_eloquent($query);

        return $eloquent->delete();
    }

    /**
     * convert query to eloquent
     * can filter by: id
     *
     * @param array $query
     * @param null $eloquent
     * @return CommonQuestion|Builder
     */
    public static function query_to_eloquent (array $query = [], $eloquent = null)
    {
        if ($eloquent === null)
        {
            $eloquent = new CommonQuestion();
        }

        if (isset($query['id']))
        {
            $eloquent = $eloquent->where('id', $query['id']);
        }

        return $eloquent;
    }
}
