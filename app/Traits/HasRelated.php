<?php

namespace App\Traits;

use Closure;
use Illuminate\Support\Facades\DB;

trait HasRelated
{
    /**
     * Simple collaborative  filtering.
     *
     * @see https://arctype.com/blog/collaborative-filtering-tutorial/ - Thanks arctype!
     *
     * @param string $viaTable The table to join to.
     * @param string $usingColumn The column that determines the shared relationship - this is the column that will be used to find models that are related.
     * @param \Closure|null $callback - Optional callback to modify the query. Use it to add further with() constraints.
     * @return Collection
     */
    public function getRelatedModels(string $viaTable, string $usingColumn, ?Closure $callback = null)
    {
        $finalQuery =
            $this->query()
            ->join($viaTable, function ($join) use ($viaTable) {
                $join->on(
                    $viaTable . "." . $this->getForeignKey(),
                    "=",
                    $this->getTable() . "." . $this->primaryKey
                );
            })
            ->select($this->getTable() . ".*", DB::raw("COUNT(" . $viaTable . "." . $usingColumn . ") as score"))
            ->whereIn($viaTable . "." . $usingColumn, function ($query) use (
                $viaTable,
                $usingColumn
            ) {
                $query->from($viaTable)
                    ->select($usingColumn)
                    ->where(
                        $this->getForeignKey(),
                        "=",
                        $this->{$this->primaryKey}
                    );
            })
            ->where(
                $viaTable . "." . $this->getForeignKey(),
                "<>",
                $this->{$this->primaryKey}
            )
            ->orderBy("score", "desc")
            ->groupBy(
                // These are the actual group by columns that are important for this query
                $viaTable . "." . $this->getForeignKey(),

                // These fields are here because needed by mysql full group by
                ...$this->currentTableColumns(),
            )
            ->withCount($viaTable);

        // Call any callback if provided.
        if ($callback instanceof Closure && $callback !== null) {
            $finalQuery = $callback($finalQuery);
        }

        return $finalQuery->get();
    }

    private function currentTableColumns()
    {
        $columns = $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
        return $columns;
    }
}
