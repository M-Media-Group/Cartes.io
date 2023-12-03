<?php

namespace App\Helpers;

use App\Models\Category;
use App\Models\Map;
use DantSu\OpenStreetMapStaticAPI\LatLng;
use DantSu\OpenStreetMapStaticAPI\Markers;
use DantSu\OpenStreetMapStaticAPI\OpenStreetMap;
use DantSu\OpenStreetMapStaticAPI\TileLayer;
use DantSu\PHPImageEditor\Image;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class MapImageGenerator
{
    /**
     * The width of the image
     *
     * @var integer
     */
    protected $width = 600;

    /**
     * The height of the image
     *
     * @var integer
     */
    protected $height = 480;

    /**
     * The height of the icon
     *
     * @var integer
     */
    protected $iconHeight = 30;

    /**
     * The width of the icon
     *
     * @var integer
     */
    protected $iconWidth = 30;

    /**
     * The zoom level of the image
     *
     * @var integer
     */
    protected $zoom = 5;

    /**
     * The response type. This can be either 'png' or 'base64'.
     *
     * @var string
     */
    protected $responseType = 'png';

    /**
     * The latitude of the image center
     *
     * @var float
     */
    protected $lat = 0;

    /**
     * The longitude of the image center
     *
     * @var float
     */
    protected $lng = 0;

    /**
     * The maximum age of the cache in seconds. By default, this is 6 hours. This is used to cache the image and prevent abuse, as well as speed up the response time significantly.
     *
     * @var integer
     */
    protected $cacheMaxAge = 21600;

    /**
     * The allowed widths for the image. Setting an array of allowed widths helps prevent abuse by limiting the number of possible image sizes, and therefore the number of possible cache keys.
     *
     * @var array
     */
    public $allowedWidths = [600, 800, 1200];

    /**
     * The allowed heights for the image. Setting an array of allowed heights helps prevent abuse by limiting the number of possible image sizes, and therefore the number of possible cache keys.
     *
     * @var array
     */
    public $allowedHeights = [480, 600, 800];

    /**
     * Allowed response types
     *
     * @var array
     * @todo implement/actually use, current unused.
     */
    public $allowedResponseTypes = [
        [
            "name" => "png",
            "type" => "image/png",
        ],
        [
            "name" => "base64",
            "type" => "text/plain",
        ]
    ];

    /**
     * Path to the base marker image
     *
     * @var string
     */
    protected $pathToBaseMarkerImage = 'images/vendor/leaflet/dist/marker-icon.png';

    /**
     * Undocumented function
     *
     * @param integer|null $width
     * @param integer|null $height
     * @param integer|null $zoom
     * @param float|null $lat
     * @param float|null $lng
     * @param string|null $output
     */
    public function __construct(int $width = null, int $height = null, int $zoom = null, float $lat = null, float $lng = null, string $output = null)
    {
        $this->updateImageCenter($lat, $lng, $zoom);
        $this->updateImageDimensions($width, $height);
        $this->updateResponseType($output);
    }

    /**
     * Get the cache key for the a given resource and its ID. This is used to cache the image based on the resource and its ID, dimensions, zoom, and response type.
     *
     * @param string $resource
     * @param string|int $id
     * @return string
     */
    public function getCacheKey(string $resource, string|int $id): string
    {
        return
            $resource . '-' . $id . '-static-image-' . $this->width . 'x' . $this->height . 'x' . $this->zoom . '.' . $this->responseType;
    }

    /**
     * Generate the image for the map
     *
     * @param Map $map
     * @return string
     */
    public function generateForMap(Map $map)
    {

        // If the map has a center, and both lat and lng, we update the center
        if (optional($map->center)->lat && optional($map->center)->lng) {
            $this->updateImageCenter($map->center->lat, $map->center->lng);
        }

        if (!$this->canGenerateImage()) {
            throw new \Exception('Cannot generate image. Missing required parameters.');
        }

        // We will use the dantsu/php-osm-static-api package to generate the static map image
        $generatedMap = new OpenStreetMap(
            new LatLng($this->lat, $this->lng),
            $this->zoom,
            $this->width,
            $this->height,
            $this->getDefaultTileLayer()
        );

        // Group by category
        $categoryGroupedMarkers = $map->activeMarkers->groupBy('category_id');

        // Add the markers for each category
        foreach ($categoryGroupedMarkers as $markers) {
            $generatedMap->addMarkers($this->generateMarkersForCategory($markers->first()->category, $markers));
        }

        // When the dependency creates an image using cURL, it expects REQUEST_SCHEME, HTTP_HOST, and REQUEST_URI to be set, otherwise it will crash with a fatal error. The problem is in vendor/dantsu/php-image-editor/src/Image.php in the `curl` method. So we set them here. @see https://github.com/DantSu/php-image-editor/pull/8
        if (!isset($_SERVER['REQUEST_SCHEME'])) {
            $_SERVER['REQUEST_SCHEME'] = 'http';
        }
        if (!isset($_SERVER['HTTP_HOST'])) {
            $_SERVER['HTTP_HOST'] = config('app.url');
        }
        if (!isset($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = '/';
        }

        // Get the image (returns as PHPImageEditor Image), then encode to base64, then return
        $image = $generatedMap->getImage();

        if ($this->responseType === 'base64') {
            return $image->getBase64PNG();
        } elseif ($this->responseType === 'png') {
            return $image->getDataPNG();
        }

        return $image;
    }

    /**
     * Generate markers for a category
     *
     * @param Category $category
     * @param array|Collection $markers
     * @return Markers
     */
    public function generateMarkersForCategory(Category $category, array|Collection $markers): Markers
    {
        // Get the marker image for the category
        $markerImage = $this->getOrGenerateMarkerImageForCategory($category);

        // Create the marker
        $generatedMarkers = new Markers($markerImage);

        foreach ($markers as $marker) {
            $generatedMarkers->addMarker(new LatLng($marker->y, $marker->x));
        }

        return $generatedMarkers;
    }

    /**
     * Get the marker image for the category from the cache, or generate it if it doesn't exist.
     *
     * We use the cache here to reduce the number of requests to the external icons provider. It should also speed up the response time because we don't have to download the image every time.
     *
     * @param \App\Models\Category $category
     * @return \DantSu\PHPImageEditor\Image
     */
    public function getOrGenerateMarkerImageForCategory(Category $category): Image
    {
        $cacheKey = $this->getCacheKey('category', $category->id);

        if (cache()->has($cacheKey)) {
            return Image::fromBase64(cache()->get($cacheKey));
        }

        $icon = $this->getBestMarkerIcon($category->full_icon_url);

        $markerImage = Image::fromPath($icon);

        // Resize the image to the icon dimensions
        $markerImage->downscaleProportion($this->iconWidth, $this->iconHeight);

        // Cache as base64
        cache()->put($cacheKey, $markerImage->getBase64PNG(), $this->cacheMaxAge);

        return $markerImage;
    }

    /**
     * Update the image center. If null is passed, it will use the default image center.
     *
     * @param float|null $lat
     * @param float|null $lng
     * @param integer|null $zoom
     * @return void
     */
    public function updateImageCenter(float $lat = null, float $lng = null, int $zoom = null): void
    {
        $this->lat = $lat ?? $this->lat;
        $this->lng = $lng ?? $this->lat;
        $this->zoom = $zoom ?? $this->zoom;
    }

    /**
     * Update the image dimensions. If null is passed, it will use the default image dimensions.
     *
     * @param integer|null $width
     * @param integer|null $height
     * @return void
     */
    public function updateImageDimensions(int $width = null, int $height = null): void
    {
        $this->width = $width ?? $this->width;
        $this->height = $height ?? $this->height;
    }

    /**
     * Update the response type. This can be either 'png' or 'base64'. If null is passed, it will use the default response type.
     *
     * @param string|null $type
     * @return void
     */
    public function updateResponseType(string $type = null): void
    {
        $this->responseType = $type ?? $this->responseType;
    }

    /**
     * Get the image for the map, or generate it if it doesn't exist
     *
     * @param Map $map
     * @return string
     */
    public function getOrGenerateForMap(Map $map)
    {
        $cacheKey = $this->getCacheKey('map', $map->uuid);

        if (cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }

        $mapImage = $this->generateForMap($map);

        cache()->put($cacheKey, $mapImage, $this->cacheMaxAge);

        return $mapImage;
    }

    /**
     * Get the Content-Type header value
     *
     * @return string
     */
    public function getContentTypeHeaderValue(): string
    {
        return $this->responseType === 'png' ? 'image/png' : 'text/plain';
    }

    /**
     * Get the Cache-Control header value
     *
     * @return string
     */
    public function getCacheControlHeaderValue(): string
    {
        return 'public, max-age=' . $this->cacheMaxAge;
    }

    /**
     * Get all the headers for the response. This includes the Content-Type and Cache-Control headers.
     *
     * @return array
     */
    public function getAllHeaders(): array
    {
        return [
            'Content-Type' => $this->getContentTypeHeaderValue(),
            'Cache-Control' => $this->getCacheControlHeaderValue()
        ];
    }

    /**
     * Check if we have all the required parameters to generate an image
     *
     * @return boolean
     */
    public function canGenerateImage()
    {
        return $this->width && $this->height && $this->zoom && $this->lat !== null && $this->lng !== null;
    }

    /**
     * Get the best marker icon for the category. If the icon doesn't exist, use the default marker icon. If the icon is unsupported, use the default marker icon.
     *
     * @param string $path
     * @return string
     */
    public function getBestMarkerIcon(string $path): string
    {
        // If the file ends in .svg, use the default marker icon
        if (Str::endsWith($path, '.svg')) {
            return $this->pathToBaseMarkerImage;
        }

        // If the path starts with http, check if the file exists
        if (Str::startsWith($path, 'http')) {
            $headers = get_headers($path);
            $fileExists = Str::startsWith($headers[0], 'HTTP/1.1 200 OK');

            if (!$fileExists) {
                return $this->pathToBaseMarkerImage;
            }

            return $path;
        }

        // If the file doesn't exist, use the default marker icon
        if (!file_exists(public_path($path))) {
            return $this->pathToBaseMarkerImage;
        }

        // If the file exists, return the path
        return $path;
    }

    /**
     * Get the default tile layer
     *
     * @return TileLayer
     */
    public function getDefaultTileLayer()
    {
        return new TileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png', '© ' . config('app.name') . ' | OpenStreetMap | CARTO | Icons8');
    }
}
