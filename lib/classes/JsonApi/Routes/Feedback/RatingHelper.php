<?php

namespace JsonApi\Routes\Feedback;

use FeedbackElement;

trait RatingHelper
{
    private function getRating(FeedbackElement $element, int $rating): int
    {
        $mode = intval($element['mode']);

        if ($mode === 0) {
            return 0;
        }

        if ($rating === 0) {
            return 1;
        }

        if ($mode === 1) {
            return min(5, $rating);
        }

        if ($mode === 2) {
            return min(10, $rating);
        }

        throw new InvalidArgumentException("Invalid mode {$mode}");
    }
}
