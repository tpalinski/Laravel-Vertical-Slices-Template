<?php

declare(strict_types=1);

namespace Core\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

/**
 * @template T of Model
 */
abstract class Repository
{
    /**
     * @return class-string<T>
     */
    abstract protected function model(): string;

    /**
     * Get a model by ID.
     *
     * @return T|null
     * @throws QueryException
     */
    public function get(int $id): ?Model
    {
        return $this->model()::query()->find($id);
    }

    /**
     * Get a model by ID or fail.
     *
     * @return T
     * @throws ModelNotFoundException
     * @throws QueryException
     */
    public function getOrFail(int $id): Model
    {
        return $this->model()::query()->findOrFail($id);
    }

    /**
     * Get all models.
     *
     * @return Collection<int, T>
     * @throws QueryException
     */
    public function all(): Collection
    {
        return $this->model()::query()->get();
    }

    /**
     * Get models by a specific field.
     *
     * @return Collection<int, T>
     * @throws QueryException
     */
    public function getAllByField(string $field, mixed $value): Collection
    {
        return $this->model()::query()
            ->where($field, $value)
            ->get();
    }

    /**
     * Get model by a specific field.
     *
     * @return T|null
     * @throws QueryException
     */
    public function getByField(string $field, mixed $value): ?Model
    {
        return $this->model()::query()
            ->where($field, $value)
            ->first();
    }

    /**
     * Persist a new model.
     *
     * @param T $model
     * @return T
     * @throws QueryException
     */
    public function create(Model $model): Model
    {
        $model->saveOrFail();
        return $model;
    }

    /**
     * Update an existing model instance.
     *
     * @param T $model
     * @return T
     * @throws QueryException
     */
    public function update(Model $model): Model
    {
        $model->saveOrFail();
        return $model;
    }

    /**
     * Delete a model instance.
     *
     * @param T $model
     * @return bool
     * @throws QueryException
     */
    public function deleteModel(Model $model): bool
    {
        return (bool) $model->delete();
    }

    /**
     * Delete by ID.
     *
     * @param int $id
     * @return bool
     * @throws QueryException
     */
    public function deleteById(int $id): bool
    {
        return (bool) $this->model()::query()->whereKey($id)->delete();
    }
}
