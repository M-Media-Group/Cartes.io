<?php

namespace App\Parsers\Files;

class GPXParser extends FIleParser
{
    /**
     * Parse the GPX file.
     *
     * @param string $filepath
     * @return array
     */
    public function parseFile(string $filepath): array
    {
        $xml = simplexml_load_file($filepath);
        $json = json_encode($xml);
        $array = json_decode($json, true);

        $markers = [];

        if (isset($array['wpt'])) {
            foreach ($array['wpt'] as $marker) {
                // If the track name is not a string, we can't use it as a category name, so we use "Track" as the default
                if (
                    !isset($marker['name']) ||
                    !is_string($marker['name']) ||
                    empty($marker['name'])
                ) {
                    $marker['name'] = 'Waypoint';
                }

                if (
                    !isset($marker['desc']) ||
                    !is_string($marker['desc']) ||
                    empty($marker['desc'])
                ) {
                    $marker['desc'] = null;
                }

                $markers[] = [
                    'lat' => $marker['@attributes']['lat'],
                    'lng' => $marker['@attributes']['lon'],
                    'category_name' => $marker['name'] ?? 'Waypoint',
                    'description' => $marker['desc'] ?? null,
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

                $markers[] = [
                    'description' => $track['desc'],
                    'locations' => $markerlocations,
                    // The category_name is the name of the track - parsed CDATA
                    'category_name' => $track['name'],
                ];
            }
        }

        return $markers;
    }
}
