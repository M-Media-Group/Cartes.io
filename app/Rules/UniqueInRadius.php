<?php

namespace App\Rules;

use App\Incident;
use Illuminate\Contracts\Validation\Rule;

class UniqueInRadius implements Rule
{
    public $radius;
    public $map_id;
    public $category_id;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($radius = 20, $map_id = null, $category_id = null)
    {
        $this->radius = $radius;
        $this->map_id = $map_id;
        $this->category_id = $category_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $incidents = Incident::distanceSphere('location', $value, $this->radius)
            ->when($this->category_id, function ($query) {
                return $query->where('category_id', $this->category_id);
            })
            ->when($this->map_id, function ($query) {
                return $query->where('map_id', $this->map_id);
            })
            ->first();
        if ($incidents) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The report location is invalid because there is an active existing report already in the area. Please don\'t create duplicate reports.';
    }
}
