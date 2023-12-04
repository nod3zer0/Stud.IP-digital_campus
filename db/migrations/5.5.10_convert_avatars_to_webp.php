<?php
final class ConvertAvatarsToWebp extends Migration
{
    const SIZES = [
        'small'  => [
            'default' => [25 * 2, 25 * 2],
            'license' => [60 * 2, 20 * 2],
        ],
        'medium' => [
            'default' => [100 * 2, 100 * 2],
            'license' => [120 * 2, 40 * 2],
        ],
        'normal' => [
            'default' => [250 * 2, 250 * 2],
            'license' => [300 * 2, 100 * 2],
        ],
    ];

    protected function up()
    {
        foreach (['user', 'course', 'institute', 'licenses'] as $type) {
            $source_directory = $GLOBALS['DYNAMIC_CONTENT_PATH'] . '/' . $type;

            // Convert original images
            $iterator = new RegexIterator(
                new FilesystemIterator($source_directory),
                '/_original\.png$/i'
            );
            foreach ($iterator as $file) {
                $this->convert($file, $type);
            }

            // Convert leftover images
            $iterator = new RegexIterator(
                new FilesystemIterator($source_directory),
                '/_normal\.png$/i'
            );
            foreach ($iterator as $file) {
                $this->convert($file, $type);
            }
        }
    }

    private function convert(
        SplFileInfo $input_file,
        string $type
    ): void {
        $input_image = imagecreatefromstring(file_get_contents($input_file->getPathname()));

        if ($input_image === false) {
            unlink($input_file->getPathname());
            return;
        }

        $user_id = explode('_', $input_file->getBasename('.png'))[0];
        $output_path = $input_file->getPath();
        if ($type !== 'licenses') {
            $output_path .= '/' . substr($user_id, 0, 2);
        }
        if (!is_dir($output_path)) {
            mkdir($output_path);
        }

        imagepalettetotruecolor($input_image);
        imagealphablending($input_image, false);
        imagesavealpha($input_image, true);

        $image_width = imagesx($input_image);
        $image_height = imagesy($input_image);

        foreach (array_keys(self::SIZES) as $size) {
            [$width, $height] = self::SIZES[$size][$type] ?? self::SIZES[$size]['default'];
            $output_file = "{$output_path}/{$user_id}_{$size}.webp";

            $factor = min($width / $image_width, $height / $image_height);
            $resized_width  = round($image_width * $factor);
            $resized_height = round($image_height * $factor);

            $xpos = intval($width - $resized_width) >> 1;
            $ypos = intval($height - $resized_height) >> 1;

            $output_image = $this->createNewImage($width, $height);

            imagecopyresampled(
                $output_image, $input_image,
                $xpos, $ypos,
                0, 0,
                $resized_width, $resized_height,
                $image_width, $image_height
            );

            imagewebp($output_image, $output_file, 90);
            imagedestroy($output_image);
        }

        imagedestroy($input_image);

        unlink($input_file->getPath() . '/' . $user_id . '_original.png');
        foreach (array_keys(self::SIZES) as $size) {
            unlink($input_file->getPath() . '/' . $user_id . '_' . $size . '.png');
            unlink($input_file->getPath() . '/' . $user_id . '_' . $size . '@2x.png');
        }
    }

    private function createNewImage(int $width, int $height)
    {
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, false); // Otherwise, WebP won't store the alpha information

        $transparent_color = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent_color);

        return $image;
    }
}
