<?php

namespace App\Parsers\Files;

class GeoJSONParser extends FIleParser
{
    public function parseMarkersFromFile(string $filepath): array
    {
        $data = $this->readFile($filepath);

        $markers = [];

        if (isset($data['features'])) {
            foreach ($data['features'] as $feature) {
                // Depending on the type of geometry, the coordinates can be in different places
                // If its just a point, the coordinates are in the first level of the geometry object
                if ($feature['geometry']['type'] === 'Point') {
                    $markers[] = [
                        'lat' => $feature['geometry']['coordinates'][1],
                        'lng' => $feature['geometry']['coordinates'][0],
                        'category_name' => $feature['properties']['name'] ?? 'Feature',
                        'description' => $feature['properties']['description'] ?? null,
                        'link' => $feature['properties']['link'] ?? null,
                        'elevation' => $feature['properties']['elevation'] ?? null,
                        'created_at' => $feature['properties']['time'] ?? null,
                        'updated_at' => $feature['properties']['time'] ?? null,
                        'meta' => $feature['properties'],
                    ];
                } elseif ($feature['geometry']['type'] === 'LineString') {
                    // Else if its a lineString, we have to add it to markers.locations for each coordinate

                    $locations = [];

                    $pointers = 0;
                    foreach ($feature['geometry']['coordinates'] as $coordinate) {
                        $locations[] = [
                            'lat' => $coordinate[1],
                            'lng' => $coordinate[0],
                            'elevation' => $coordinate[2] ?? null,

                            // There may be a coordTimes array in the properties, which we can use to set the created_at and updated_at
                            'created_at' => $feature['properties']['coordTimes'][$pointers] ?? null,
                            'updated_at' => $feature['properties']['coordTimes'][$pointers] ?? null,
                        ];
                    }

                    $markers[] = [
                        'category_name' => $feature['properties']['name'] ?? 'Feature',
                        'description' => $feature['properties']['description'] ?? null,
                        'link' => $feature['properties']['link'] ?? null,
                        'created_at' => $feature['properties']['time'] ?? null,
                        'updated_at' => $feature['properties']['time'] ?? null,
                        'meta' => $feature['properties'],
                        'locations' => $locations,
                    ];
                }
            }
        }

        return $markers;
    }

    public function parseMapDetailsFromFile(string $filepath): array
    {
        return [
            'name' => pathinfo($filepath, PATHINFO_FILENAME),
            'description' => 'A map of ' . pathinfo($filepath, PATHINFO_FILENAME),
        ];
    }

    public function readFile(string $filepath): mixed
    {
        $json = file_get_contents($filepath);
        return json_decode($json, true);
    }
}
