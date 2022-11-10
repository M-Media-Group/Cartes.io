<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Queryable
{
    /**
     * The allowed operators in a given query.
     *
     * @var array<string>
     */
    private $allowedOperators = [
        '>=',
        '<=',
        '!=',
        '>',
        '<',
        ':',
        '~',
        '=',
    ];

    /**
     * The mapping between query operators and their respective where() operators.
     *
     * @var array
     */
    private $operatorsToLaravelWhere = [
        '~' => 'like',
        ':' => '=',
    ];

    /**
     * Allow and parse a query parameter in the request.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeParseQuery(Builder $query)
    {
        // request()->input('query') trimmed to max 50 chars
        $queryString = substr((string) request()->input('query'), 0, 50);
        $queries = explode('AND', $queryString);
        foreach ($queries as $q) {
            // If the query contains "OR", skip it
            if (str_contains($q, 'OR')) {
                continue;
            }
            $parsedQuery = $this->parseQueryParam($q);
            $query = $this->addWhereOrHavingClause($query, $parsedQuery);
        }

        $queries = explode('OR', $queryString);
        foreach ($queries as $q) {
            if (str_contains($q, 'AND')) {
                continue;
            }
            $parsedQuery = $this->parseQueryParam($q);
            $query = $this->addWhereOrHavingClause($query, $parsedQuery);
        }

        return $query;
    }

    /**
     * Get the exploded value from a string with underscores.
     *
     * @param  string  $value
     * @return array<string>
     */
    private function getExplodedValue(string $value)
    {
        $parts = explode('_', $value);
        $last = array_pop($parts);

        return [implode('_', $parts), $last];
    }

    /**
     * Add a where or having clause to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  object  $parsedQuery
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function addWhereOrHavingClause(Builder $query, $parsedQuery)
    {
        if (! $parsedQuery->parameter) {
            return $query;
        }
        // If the parameter uses dot notation, we need to split it up
        if (str_contains($parsedQuery->parameter, '.')) {
            [$relation, $parameter] = $this->explodeDotNotation($parsedQuery->parameter);

            return $this->appendWhereHas($query, $relation, $parameter, $parsedQuery->operator, $parsedQuery->value);
        }
        $explodedValue = $this->getExplodedValue($parsedQuery->parameter);
        if (! isset($this->attributes[$parsedQuery->parameter]) && $explodedValue[1] === 'count') {
            // We need to use a HAVING statement in this case
            $query->having($parsedQuery->parameter, $parsedQuery->operator, $parsedQuery->value);
        } else {
            $query->where($parsedQuery->parameter, $parsedQuery->operator, $parsedQuery->value);
        }

        return $query;
    }

    /**
     * Recursively append a whereHas if the query parameter is dot notation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $relation
     * @param  string  $parameter
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function appendWhereHas(Builder $query, string $relation, string $parameter, $operator, $value)
    {
        return $query->whereHas($relation, function ($q) use ($parameter, $operator, $value) {
            if (str_contains($parameter, '.')) {
                [$relation, $parameter] = $this->explodeDotNotation($parameter);

                return $this->appendWhereHas($q, $relation, $parameter, $operator, $value);
            } else {
                return $q->where($parameter, $operator, $value);
            }
        });
    }

    /**
     * Explode the dot notation of a parameter.
     *
     * @param  string  $parameter
     * @return array<string>
     */
    private function explodeDotNotation(string $parameter)
    {
        $exploded = explode('.', $parameter);
        $relation = array_shift($exploded);
        $parameter = implode('.', $exploded);

        return [$relation, $parameter];
    }

    /**
     * Parse a query parameter.
     *
     * @param  string  $query
     * @return object
     */
    private function parseQueryParam(string $query): object
    {
        $parameter = null;
        $operator = null;
        $value = null;

        $query = trim($query);

        // For each allowedOperator
        foreach ($this->allowedOperators as $allowedOperator) {
            // If the query contains the allowedOperator
            if (str_contains($query, $allowedOperator)) {
                // Split the query based on the allowedOperator
                $query = explode($allowedOperator, $query);

                // Set the parameter to the first part of the split query
                $parameter = trim($query[0]);

                // Set the operator to the second part of the split query
                $operator = $allowedOperator;

                // If operatorsToLaravelWhere has the operator
                if (array_key_exists($operator, $this->operatorsToLaravelWhere)) {
                    $operator = $this->operatorsToLaravelWhere[$operator];
                }

                // Set the value to the third part of the split query
                $value = $this->prepareValueForWhereClause(trim($query[1]), $operator);

                // Break out of the loop
                break;
            }
        }
        // Return an object
        return (object) [
            'parameter' => $parameter,
            'operator' => $operator,
            'value' => $value,
        ];
    }

    /**
     * Prepare the value for a where clause.
     *
     * @param  string  $value
     * @param  string  $operator
     * @return string|bool|null
     */
    private function prepareValueForWhereClause(string $value, string $operator)
    {
        //  If the value is "null", return null
        if (strtoupper($value) == 'NULL') {
            return null;
        }

        // if the operator is 'like', then we need to add '%' to the value
        if (strtolower($operator) == 'like') {
            return '%'.$value.'%';
        }

        // If the operator is 'true'
        if (strtolower($operator) == 'true') {
            return true;
        }

        // If the operator is 'false'
        if (strtolower($operator) == 'false') {
            return false;
        }

        return $value;
    }
}
