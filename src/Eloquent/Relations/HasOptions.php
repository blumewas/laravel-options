<?php

namespace blumewas\LaravelOptions\Eloquent\Relations;

use blumewas\LaravelOptions\BaseOptions;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 * @template TResult
 *
 * @extends \Illuminate\Database\Eloquent\Relations\HasOneOrMany<TRelatedModel, TDeclaringModel, TResult>
 */
class HasOptions extends HasOneOrMany
{
    /**
     * The options group.
     */
    protected ?string $optionsGroup = null;

    /**
     * The options class.
     *
     * @var class-string<BaseOptions>
     */
    protected string $optionClass;

    /**
     * Create a new morph one or many relationship instance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<TRelatedModel>  $query
     * @param  TDeclaringModel  $parent
     * @param  string  $type
     * @param  string  $id
     * @param  string  $localKey
     * @param  class-string<BaseOptions>  $optionClass
     * @return void
     */
    public function __construct(
        Builder $query,
        Model $parent,
        $foreignKey,
        $localKey,
        string $optionClass,
    ) {
        $this->optionsGroup = $this->getOptionsGroup($parent);

        parent::__construct($query, $parent, $foreignKey, $localKey);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            $query = $this->getRelationQuery();

            $query->where('group', $this->optionsGroup);
        }
    }

    /** {@inheritDoc} */
    public function addEagerConstraints(array $models)
    {
        $this->whereInEager(
            'whereIn',
            'group',
            $this->getOptionGroups($models),
            $this->getRelationQuery()
        );
    }

    /**
     * Get the key value of the parent's local key.
     *
     * @return mixed
     */
    public function getParentKey()
    {
        return $this->optionsGroup;
    }

    /**
     * Get the results of the relationship.
     *
     * @return TResult
     */
    public function getResults()
    {
        return ! is_null($this->getParentKey())
                ? $this->query->get()
                : $this->related->newCollection();
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array<int, TDeclaringModel>  $models
     * @param  string  $relation
     * @return array<int, TDeclaringModel>
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array<int, TDeclaringModel>  $models
     * @param  \Illuminate\Database\Eloquent\Collection<int, TRelatedModel>  $results
     * @param  string  $relation
     * @return array<int, TDeclaringModel>
     */
    public function match(array $models, Collection $results, $relation)
    {
        return $this->matchMany($models, $results, $relation);
    }

    protected function getOptionGroups(array $models): array
    {
        return array_map(function (Model $model) {
            return $this->getOptionsGroup($model);
        }, $models);
    }

    /**
     * Get the options group.
     *
     * @param  TDeclaringModel  $parent
     * @param  class-string<BaseOptions>  $optionClass
     */
    protected function getOptionsGroup(Model $parent): ?string
    {

        // if ($optionClass === null) {
        //     $optionClass = $parent->getOptionsClass();
        // }

        // if ($optionClass === null || ! is_a($optionClass, BaseOptions::class, true)) {
        //     throw new \InvalidArgumentException('Option class not set.');
        // }

        return $parent->getOptionKey();
    }
}
