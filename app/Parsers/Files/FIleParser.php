<?php

namespace App\Parsers\Files;

abstract class FIleParser
{
    /**
     * Parse the filetype. This function must be implemented by all parsers.
     *
     * @param string $filepath
     * @return array{map: array, markers: array}
     */
    public function parseFile(string $filepath): array
    {
        return [
            'map' => $this->parseMapDetailsFromFile($filepath),
            'markers' => $this->parseMarkersFromFile($filepath),
        ];
    }

    /**
     * Parse the filetype. This function must be implemented by all parsers.
     *
     * @param string $filepath
     * @return array{category_name?: string, description?: string, link?: string, created_at?: string, updated_at?: string, meta: array, lat?: float, lng?: float, elevation?: float, locations?: array}
     */
    abstract public function parseMarkersFromFile(string $filepath): array;

    /**
     * Parse the map details from the file.
     *
     * @param string $filepath
     * @return array{title?: string, description?: string}
     */
    abstract public function parseMapDetailsFromFile(string $filepath): array;

    /**
     * Read the data from the file and return its contents for further processing.
     *
     * @param string $filepath
     * @return mixed
     */
    public function readFile(string $filepath): mixed
    {
        return file_get_contents($filepath);
    }
}
