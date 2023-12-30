<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MarkerGeoJsonResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'features';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        // The following props are to be skipped and not put in the properties object
        $skipProps = [
            'x',
            'y',
            'location',
            'id',
            'elevation',
        ];

        // All the rest of the properties available on the model, except the ones we want to skip, should be in an array. For this, we need to get the object as Laravel would provide it, which would hide all the properties we want to skip, as well as all those in $hidden.
        $properties = collect($this->resource)->except($skipProps)->toArray();

        // dd(get_debug_type($this->address), get_debug_type($this->x));
        // We will return a geoJSON feature object
        // https://tools.ietf.org/html/rfc7946#section-3.2
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [
                    $this->x,
                    $this->y,
                    $this->elevation,
                ],
            ],
            'id' => $this->id,
            'properties' => [
                ...$properties
            ],
        ];

        return parent::toArray($request);
    }
}
