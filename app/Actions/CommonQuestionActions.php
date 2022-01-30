<?php

namespace App\Actions;

use App\Exceptions\CustomException;
use App\Services\PaginationService;
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
}
