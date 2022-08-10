<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

trait Expandable
{

    /**
     * The relationships to expand
     *
     * @var array
     */
    protected $expandableFields = [];

    /**
     * The count of relationships to retrieve
     *
     * @var array
     */
    protected $expandableFieldCounts = [];

    /**
     * The scopes to apply to the model.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * The parameters that the request accepts that do not really change the query.
     *
     * @var array
     */
    protected $parameters = [
        'per_page' => 15,
        'page' => 1,
        'sort' => 'id',
        'order' => 'asc',
    ];

    /**
     * An instance of the reflection class for the current model
     *
     * @var \ReflectionClass
     */
    protected \ReflectionClass $reflection;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->reflection = $this->getReflectionInstance();
        $this->expandableFields = $this->getAllRelationships();
        $this->expandableFieldCounts = $this->getAllRelationships();
        $this->filters = $this->getAllScopes();
    }

    /**
     * Get an instance of the reflection class.
     *
     * @return \ReflectionClass
     */
    private function getReflectionInstance()
    {
        return new \ReflectionClass(Self::class);
    }

    /**
     * Get the models available scopes.
     *
     * @return void
     */
    private function getAllScopes()
    {
        $scopes = [];
        foreach ($this->reflection->getMethods() as $reflectionMethod) {
            if (str_starts_with($reflectionMethod->name, 'scope') && \Reflection::getModifierNames($reflectionMethod->getModifiers())[0] === 'public') {
                $scopes[] = lcfirst(substr($reflectionMethod->name, 5));
            }
        }
        return $scopes;
    }

    /**
     * Get all of the models relationships
     *
     * @return void
     */
    public function getAllRelationships()
    {
        return collect($this->reflection->getMethods())
            ->filter(
                fn ($method) => !empty($method->getReturnType()) &&
                    str_contains(
                        $method->getReturnType(),
                        'Illuminate\Database\Eloquent\Relations'
                    )
            )
            ->pluck('name')
            ->all();
    }

    /**
     * Get all of the models expandable fields
     *
     * @return void
     */
    public function getExpandableFields()
    {
        return $this->expandableFields;
    }

    /**
     * Validate the expansion scopes
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    private function validateScopeExpand(Request $request)
    {
        $request->validate([
            'with' => 'array',
            'with.*' => ['string', Rule::in(
                $this->getExpandableFields()
            )],
            'with.*.*' => 'prohibited',

            'withCount' => 'array',
            'withCount.*' => ['string', Rule::in(
                $this->getExpandableFields()
            )],
        ]);
    }

    /**
     * Expand the request
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function scopeExpand(Builder $query)
    {
        $request = request();

        $this->validateScopeExpand($request);

        return $query
            ->when($request->input('withCount'), function ($q) use ($request) {
                $q->withCount($request->input('withCount'));
            })
            ->when($request->input('with'), function ($q) use ($request) {
                $q->with($request->input('with'));
            });
    }

    /**
     * Validate the filter scopes
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    private function validateScopeFilter(Request $request)
    {
        $request->validate([
            'scopes' => 'array',
            'scopes.*' => Rule::in(
                array_merge(
                    $this->filters
                )
            ),
        ]);
    }

    /**
     * Filter the request
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function scopeFilter(Builder $query)
    {
        $request = request();

        $this->validateScopeFilter($request);

        foreach (Self::getFillable() as $prop) {
            $query->when($request->input($prop), function ($q) use ($request, $prop) {
                $q->where($prop, $request->input($prop));
            });
        }

        if ($request->input('scopes')) {
            foreach ($request->input('scopes') as $scope) {
                $query->$scope();
            }
        }

        return $query->when($request->input('before'), function ($q) use ($request) {
            $q->whereDate('created_at', '<=', $request->input('before'));
        })
            ->when($request->input('after'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->input('after'));
            });
    }

    /**
     * Apply all scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeFilterAndExpand(Builder $query)
    {
        $query->filter($query);
        $query->expand($query);
        return $query;
    }

    public function expandablePaginate(Builder $query)
    {
        return $query
            ->orderBy($this->parameters['sort'], $this->parameters['order'])
            ->paginate($this->parameters['per_page']);
    }
}
