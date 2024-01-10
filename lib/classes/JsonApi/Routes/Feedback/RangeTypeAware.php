<?php

namespace JsonApi\Routes\Feedback;

use FeedbackRange;
use SimpleORMap;

trait RangeTypeAware
{
    protected $possibleRangeTypes = null;

    protected function preparePossibleRangeTypes(): void
    {
        foreach (app('json-api-integration-schemas') as $class => $schema) {
            if (is_subclass_of($class, FeedbackRange::class) && is_subclass_of($class, SimpleORMap::class)) {
                $this->possibleRangeTypes[$schema::TYPE] = $class;
            }
        }
    }
}
