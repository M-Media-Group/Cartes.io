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
    abstract public function parseFile(string $filepath): array;

    /**
     * Parse the filetype. This function must be implemented by all parsers.
     *
     * @param string $filepath
     * @return array{category_name?: string, description?: string, link?: string, created_at?: string, updated_at?: string, meta: array, lat?: float, lng?: float, elevation?: float, locations?: array}
     */
    abstract public function parseMarkersFromFile(string $filepath): array;
}
