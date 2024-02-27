<?php

namespace App\Parsers\Files;

abstract class FIleParser
{
    /**
     * Parse the filetype. This function must be implemented by all parsers.
     *
     * @param string $filepath
     * @return array
     */
    abstract public function parseFile(string $filepath): array;
}
