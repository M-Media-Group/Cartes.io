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
        'trkseg',
    ];

    public function parseMarkersFromFile(string $filepath): array
    {
        $array = $this->readFile($filepath);

        $markers = [];

        if (isset($array['wpt'])) {
            foreach ($array['wpt'] as $marker) {
                $marker = $this->setMarkerDetails($marker, 'Waypoint');

                $markerLocations = [$this->buildMarkerLocationFromXmlElement($marker)];

                if (!count($markerLocations)) {
                    continue;
                }

                $markers[] = $this->buildMarker($marker, $markerLocations);
            }
        }

        if (isset($array['trk'])) {
            foreach ($array['trk'] as $track) {
                $markerLocations = [];

                if (!isset($track['trkseg'])) {
                    continue;
                }

                foreach ($track['trkseg'] as $trkseg) {
                    foreach ($trkseg as $trkpt) {
                        $markerLocations[] = $this->buildMarkerLocationFromXmlElement($trkpt);
                    }
                }

                if (!count($markerLocations)) {
                    continue;
                }

                $markers[] = $this->buildMarker($track, $markerLocations);
            }
        }

        if (isset($array['rte'])) {
            // Sometimes the rte is not an array, but a single element, so we need to handle that. We can be sure its just a single element if it directly has rtept as a child
            if (isset($array['rte']['rtept'])) {
                $array['rte'] = [$array['rte']];
            }

            foreach ($array['rte'] as $route) {
                $markerLocations = [];

                if (!isset($route['rtept'])) {
                    continue;
                }

                foreach ($route['rtept'] as $rtept) {
                    $markerLocations[] = $this->buildMarkerLocationFromXmlElement($rtept);
                }

                if (!count($markerLocations)) {
                    continue;
                }

                $markers[] = $this->buildMarker($route, $markerLocations);
            }
        }

        return $markers;
    }

    /**
     * Build a marker location from a single xml element
     *
     * @param array $element
     * @return array
     */
    private function buildMarkerLocationFromXmlElement(array $element): array
    {
        return [
            'lat' => $element['@attributes']['lat'],
            'lng' => $element['@attributes']['lon'],
            'elevation' => $element['ele'] ?? null,
            'created_at' => $element['time'] ?? null,
            'updated_at' => $element['time'] ?? null,
        ];
    }

    /**
     * Get the metadata from the marker
     *
     * @param array $marker
     * @return array
     */
    private function getMarkerMeta(array $marker): array
    {
        $markerMeta = [];

        // Add all direct children of the wpt element as metadata, except for the name and desc
        foreach ($marker as $key => $value) {
            if (in_array($key, $this->metadataKeys)) {
                continue;
            }

            $markerMeta[$key] = $value;
        }

        return $markerMeta;
    }

    /**
     * Set the marker details
     *
     * @param array $marker
     * @param string $fallbackName
     * @return array
     */
    private function setMarkerDetails(array $marker, string $fallbackName = 'Marker')
    {
        // If the route name is not a string, we can't use it as a category name, so we use "Route" as the default
        if (
            !isset($marker['name']) ||
            !is_string($marker['name']) ||
            empty($marker['name'])
        ) {
            $marker['name'] = $marker['sym'] ?? $fallbackName;
        }

        if (
            !is_string($marker['desc']) ||
            empty($marker['desc'])
        ) {
            $marker['desc'] = null;
        }

        return $marker;
    }

    /**
     * Build a marker
     *
     * @param array $marker
     * @param array $locations
     * @return array
     */
    private function buildMarker(array $marker, array $locations)
    {
        $marker = $this->setMarkerDetails($marker);

        $markerMeta = $this->getMarkerMeta($marker);

        return
            [
                'description' => $marker['desc'],
                'locations' => $locations,
                'category_name' => $marker['name'],
                'link' => $marker['link'] ?? null,
                'meta' => $markerMeta,
            ];
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
