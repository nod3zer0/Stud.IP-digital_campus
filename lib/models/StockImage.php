<?php

/**
 * @property string $id          database column
 * @property string $title       database column
 * @property string $description database column
 * @property string $license     database column
 * @property string $author      database column
 * @property string $mime_type   database column
 * @property int    $size        database column
 * @property int    $width       database column
 * @property int    $height      database column
 * @property string $palette     database column
 * @property string $tags     database column
 * @property int    $mkdate      database column
 * @property int    $chdate      database column
 */
class StockImage extends \SimpleORMap
{
    public const SIZE_ORIGINAL = 'original';
    public const SIZE_LARGE = 'large';
    public const SIZE_MEDIUM = 'medium';
    public const SIZE_SMALL = 'small';

    public static function sizes()
    {
        return [
            self::SIZE_ORIGINAL => -1,
            self::SIZE_LARGE => 2400,
            self::SIZE_MEDIUM => 1920,
            self::SIZE_SMALL => 640,
        ];
    }

    protected static function configure($config = [])
    {
        $config['db_table'] = 'stock_images';

        $config['registered_callbacks']['after_delete'][] = function ($resource) {
            if ($resource->hasFile()) {
                foreach (array_keys(self::sizes()) as $sizeName) {
                    $path = $resource->getPath($sizeName);
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }
        };

        parent::configure($config);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getPath(string $size = self::SIZE_ORIGINAL): string
    {
        return sprintf(
            '%s/public/pictures/stock-images/%s',
            $GLOBALS['STUDIP_BASE_PATH'],
            $this->getFilename($size)
        );
    }

    public function getFilename(string $size = self::SIZE_ORIGINAL): string
    {
        return sprintf(
            '%d-%s.%s',
            $this->id,
            $size,
            substr($this->mime_type, 6)
        );
    }

    /**
     * return string|null  either a string containing the public URL to the file
     *                     or null if there is still no such file
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getDownloadURL(string $size = self::SIZE_ORIGINAL)
    {
        if (!$this->hasFile()) {
            return null;
        }
        $sizes = self::sizes();
        if (!(isset($sizes[$size]) && $sizes[$size] <= $this->width)) {
            return null;
        }

        return sprintf(
            '%spictures/stock-images/%s',
            $GLOBALS['ABSOLUTE_URI_STUDIP'],
            $this->getFilename($size)
        );
    }

    /**
     * @return iterable<string,string> an associative array of sizes to URLs
     */
    public function getDownloadURLs(): iterable
    {
        return array_filter(
            array_reduce(
                array_keys(self::sizes()),
                function ($urls, $size) {
                    return array_merge($urls, [$size => $this->getDownloadURL($size)]);
                },
                []
            )
        );
    }

    public function hasFile(): bool
    {
        return !empty($this->mime_type);
    }
}
