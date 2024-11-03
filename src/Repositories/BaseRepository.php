<?php

namespace Ecs\RepositoryCommand\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    public function __construct(
        protected Model $model
    ) {}

    public function __call($method, $arguments)
    {
        return $this->model->$method(...$arguments);
    }
}
