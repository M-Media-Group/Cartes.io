<?php

namespace App\Rules;

use App\Models\MarkerLocation;
use Illuminate\Contracts\Validation\Rule;

class UniqueInRadius implements Rule
{
    public int $radius;
    public ?string $map_id;
    public ?int $category_id;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(int $radius = 20, ?string $map_id = null, ?int $category_id = null)
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
        $markerLocation = new MarkerLocation();
        $markers = $markerLocation->distanceSphere('location', $value, $this->radius)
            ->when($this->category_id, function ($query) {
                return $query->whereHas('marker', function ($marker) {
                    return
                        $marker->where('category_id', $this->category_id);
                });
            })
            ->when($this->map_id, function ($query) {
                return $query->whereHas('marker', function ($marker) {
                    return
                        $marker->where('map_id', $this->map_id);
                });
            })
            ->first();
        if ($markers) {
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
