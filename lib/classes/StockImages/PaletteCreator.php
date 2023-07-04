<?php

namespace Studip\StockImages;

use ColorThief\ColorThief;

final class PaletteCreator
{
    /**
     * @param \StockImage $stockImage
     */
    public function __invoke(\StockImage $stockImage): void
    {
        $sourceImage = $stockImage->getPath(\StockImage::SIZE_SMALL);
        $palette = ColorThief::getPalette($sourceImage, 3);
        $stockImage->palette = json_encode($palette);
        $stockImage->store();
    }
}
