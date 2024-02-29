<?php

namespace App\Parsers\Files;

class GPXParser extends FIleParser
{

    /**
     * All the keys that should not be included in the metadata
     *
     * @var array
     */
    private $metadataKeys = [
        'name',
        'desc',
        'ele',
        'sym',
        'link',
        'time',
        '@attributes',
    ];

    public function parseMarkersFromFile(string $filepath): array
    {
        $array = $this->readFile($filepath);

        $markers = [];

        if (isset($array['wpt'])) {
            foreach ($array['wpt'] as $marker) {
                // If the track name is not a string, we can't use it as a category name, so we use "Track" as the default
                if (
                    !isset($marker['name']) ||
                    !is_string($marker['name']) ||
                    empty($marker['name'])
                ) {
                    $marker['name'] = $marker['sym'] ?? 'Waypoint';
                }

                if (
                    !isset($marker['desc']) ||
                    !is_string($marker['desc']) ||
                    empty($marker['desc'])
                ) {
                    $marker['desc'] = null;
                }

                $markerMeta = [];

                // Add all direct children of the wpt element as metadata, except for the name and desc
                foreach ($marker as $key => $value) {
                    if (in_array($key, $this->metadataKeys)) {
                        continue;
                    }

                    $markerMeta[$key] = $value;
                }

                $markers[] = [
                    'lat' => $marker['@attributes']['lat'],
                    'lng' => $marker['@attributes']['lon'],
                    'category_name' => $marker['name'],
                    'description' => $marker['desc'] ?? null,
                    'link' => $marker['link'] ?? null,
                    'elevation' => $marker['ele'] ?? null,
                    'created_at' => $marker['time'] ?? null,
                    'updated_at' => $marker['time'] ?? null,
                    'meta' => $markerMeta,
                ];
            }
        }

        // We also need to add the trk. Each trk will be a single marker, and each trkpt will be a location for that marker.
        if (isset($array['trk'])) {
            foreach ($array['trk'] as $track) {
                $markerlocations = [];

                if (!isset($track['trkseg'])) {
                    continue;
                }

                foreach ($track['trkseg'] as $trkseg) {
                    foreach ($trkseg as $trkpt) {
                        $markerlocations[] = [
                            'lat' => $trkpt['@attributes']['lat'],
                            'lng' => $trkpt['@attributes']['lon'],
                            'elevation' => $trkpt['ele'] ?? null,
                            'created_at' => $trkpt['time'] ?? null,
                            'updated_at' => $trkpt['time'] ?? null,
                        ];
                    }
                }

                if (!count($markerlocations)) {
                    continue;
                }

                // If the track name is not a string, we can't use it as a category name, so we use "Track" as the default
                if (
                    !is_string($track['name']) ||
                    empty($track['name'])
                ) {
                    $track['name'] = 'Track';
                }

                if (
                    !is_string($track['desc']) ||
                    empty($track['desc'])
                ) {
                    $track['desc'] = null;
                }

                $markerMeta = [];

                // Add all direct children of the trk element as metadata, except for the name and desc
                foreach ($track as $key => $value) {
                    if (in_array($key, $this->metadataKeys)) {
                        continue;
                    }

                    $markerMeta[$key] = $value;
                }

                $markers[] = [
                    'description' => $track['desc'],
                    'locations' => $markerlocations,
                    // The category_name is the name of the track - parsed CDATA
                    'category_name' => $track['name'],
                    // Meta needs to be encoded as JSON
                    'meta' => $markerMeta,
                ];
            }
        }

        return $markers;
    }

    public function parseMapDetailsFromFile(string $filepath): array
    {
        $array = $this->readFile($filepath);

        $mapDetails = [];

        if (isset($array['metadata'])) {
            $mapDetails = $array['metadata'];
        }

        $mapTitle = null;
        $mapDescription = null;

        // Depending what is isset, we will return the name and desc of the map
        if (isset($mapDetails['name'])) {
            $mapTitle = $mapDetails['name'];
        } elseif (isset($array['name'])) {
            $mapTitle = (string) $array['name'];
        }

        if (isset($mapDetails['desc'])) {
            $mapDescription = $mapDetails['desc'];
        } elseif (isset($array['desc'])) {
            $mapDescription = (string) $array['desc'];
        }

        return [
            'title' => $mapTitle,
            'description' => $mapDescription,
        ];
    }

    // Recursively convert the SimpleXMLElement to a string.
    private function convertSimpleXMLElementToString($element)
    {
        if (is_array($element)) {
            foreach ($element as $key => $value) {
                $element[$key] = $this->convertSimpleXMLElementToString($value);
            }
        } elseif (
            $element instanceof \SimpleXMLElement
            // And there are no children
            && count($element->children()) === 0
        ) {
            $element = (string) $element;
        } elseif ($element instanceof \SimpleXMLElement) {
            // Else if it is a simpleXMLElement and it has children we need to call this function again
            $element = (array) $element;
            foreach ($element as $key => $value) {
                $element[$key] = $this->convertSimpleXMLElementToString($value);
            }
        }

        return $element;
    }

    public function readFile(string $filepath): mixed
    {
        $array = (array) simplexml_load_file($filepath);

        $finalData = [];

        $finalData = $this->convertSimpleXMLElementToString($array);

        return $finalData;
    }
}
