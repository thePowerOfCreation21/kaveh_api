<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use JetBrains\PhpStorm\ArrayShape;
use Illuminate\Database\Eloquent\Collection;

class PaginationService
{
    protected object $eloquent;

    protected array $values = [
        'skip' => 0,
        'limit' => 100
    ];

    public int $count = 0;

    public Collection $data;

    /**
     * @param Request $request
     * @param object|null $eloquent
     * @return $this
     */
    public function paginate_with_request (Request $request, object $eloquent = null): static
    {
        $values = $this->get_values_from_request($request);

        return $this->paginate($values, $eloquent);
    }

    #[ArrayShape(['skip' => "int", 'limit' => "int"])]
    public function get_values_from_request (Request $request): array
    {
        $request->validate([
            'skip' => 'numeric|min:0',
            'limit' => 'numeric|min:0|max:100',
        ]);

        return [
            'skip' => !empty($request->input('skip')) ? $request->input('skip') : 0,
            'limit' => !empty($request->input('limit')) ? $request->input('limit') : 100,
        ];
    }

    /**
     * @param array|null $values [ArrayShape(['skip' => "int", 'limit' => "int"])]
     * @param object|null $eloquent
     * @return Builder
     */
    public function get_filtered_eloquent (array $values = null, object $eloquent = null): Builder
    {
        $this->set_values($values);
        return $this->set_eloquent($eloquent)->skip($this->values['skip'])->take($this->values['limit']);
    }

    /**
     * @param object|null $eloquent
     * @return Builder|Model
     */
    public function set_eloquent (object $eloquent = null): object
    {
        return ($this->eloquent = empty($eloquent) ? $this->eloquent : $eloquent);
    }

    /**
     * @param array|null $values
     * @return array
     */
    public function set_values (array $values = null): array
    {
        return ($this->values = empty($values) ? $this->values : $values);
    }

    /**
     * @param array $values
     * @param object|null $eloquent
     * @return $this
     */
    public function paginate (array $values, object $eloquent = null): static
    {
        $this->set_eloquent($eloquent);
        $this->count = $this->eloquent->count();
        $this->data = $this->get_filtered_eloquent($values)->get();
        return $this;
    }
}
