<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Relation;

class Custom extends Relation
{
    protected $baseConstraints;

    public function __construct(Builder $query, Model $parent, Closure $baseConstraints)
    {
        $this->baseConstraints = $baseConstraints;

        parent::__construct($query, $parent);
    }

    public function addConstraints()
    {
        call_user_func($this->baseConstraints, $this);
    }

    public function addEagerConstraints(array $models)
    {
        // not implemented yet
    }

    public function initRelation(array $models, $relation)
    {
        // not implemented yet
    }

    public function match(array $models, Collection $results, $relation)
    {
        // not implemented yet
    }

    public function getResults()
    {
        return $this->get();
    }
}
