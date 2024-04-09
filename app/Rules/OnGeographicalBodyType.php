<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class OnGeographicalBodyType implements Rule
{
    public string $body_type;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $body_type = 'land')
    {
        $this->body_type = $body_type;
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
        $lat = $value->latitude;
        $lon = $value->longitude;

        $api_url = 'https://api.onwater.io/api/v1/results/' . $lon . ',' . $lat;

        try {
            $json = json_decode(file_get_contents($api_url), true);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error while trying to get geographical body type: ' . $e->getMessage());

            // If the API is down, we'll just let it pass
            return true;
        }

        // If the API is down, we'll just let it pass
        if (!isset($json['water'])) {
            return true;
        }

        if ($this->body_type == 'land' && $json['water'] == false) {
            return true;
        } elseif ($this->body_type == 'water' && $json['water'] == true) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Markers on this map must be placed on ' . $this->body_type . '.';
    }
}
