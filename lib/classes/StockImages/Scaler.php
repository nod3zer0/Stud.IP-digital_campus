<?php

namespace Studip\StockImages;

final class Scaler
{
    /**
     * @param \StockImage $stockImage
     */
    public function __invoke(\StockImage $stockImage): void
    {
        foreach (\StockImage::sizes() as $name => $width) {
            if ($name !== \StockImage::SIZE_ORIGINAL) {
                $this->scaleToWidth($stockImage, $name, $width);
            }
        }
    }

    private function scaleToWidth(\StockImage $stockImage, string $sizeName, int $targetWidth): bool
    {
        $image = $this->createImage($stockImage);
        $width = imagesx($image);
        if ($width < $targetWidth) {
            return false;
        }

        $scaledImage = imagescale($image, $targetWidth);
        imagedestroy($image);

        return $this->storeImage($stockImage, $scaledImage, $sizeName);
    }

    /**
     * @return resource the \GDImage created from the original image file
     */
    private function createImage(\StockImage $stockImage)
    {
        $type = $stockImage->mime_type;
        $lookup = [
            'image/gif' => 'imagecreatefromgif',
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/png' => 'imagecreatefrompng',
            'image/webp' => 'imagecreatefromwebp',
        ];
        if (!isset($lookup[$type])) {
            throw new \RuntimeException(_('Unsupported image type.'));
        }

        return $lookup[$type]($stockImage->getPath());
    }

    /**
     * @param resource $image the scaled image
     */
    private function storeImage(\StockImage $stockImage, $image, string $sizeName): bool
    {
        $type = $stockImage->mime_type;
        $lookup = [
            'image/gif' => 'imagegif',
            'image/jpeg' => 'imagejpeg',
            'image/png' => 'imagepng',
            'image/webp' => 'imagewebp',
        ];
        if (!isset($lookup[$type])) {
            throw new \RuntimeException(_('Unsupported image type.'));
        }

        return $lookup[$type]($image, $stockImage->getPath($sizeName));
    }
}
