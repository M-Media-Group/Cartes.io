<?php

namespace App\Parsers\Files;

class GPXParser extends FIleParser
{
    public function parseFile(string $filepath): array
    {
        return [
            'map' => $this->parseMapDetailsFromFile($filepath),
            'markers' => $this->parseMarkersFromFile($filepath),
        ];
    }

    public function parseMarkersFromFile(string $filepath): array
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
                    if ($key === 'name' || $key === 'desc' || $key === 'ele' || $key === 'sym' || $key === 'link' || $key === '@attributes') {
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
                    if ($key === 'name' || $key === 'desc' || $key === 'ele' || $key === 'trkseg' || $key === 'trkpt' || $key === '@attributes') {
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
        $xml = simplexml_load_file($filepath);
        $json = json_encode($xml);
        $array = json_decode($json, true);

        $mapDetails = [];

        if (isset($array['metadata'])) {
            $mapDetails = $array['metadata'];
        }

        return [
            'title' => $mapDetails['name'] ?? null,
            'description' => $mapDetails['desc'] ?? null,
        ];
    }
}
