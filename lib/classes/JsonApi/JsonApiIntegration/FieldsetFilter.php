<?php
namespace JsonApi\JsonApiIntegration;

class FieldsetFilter extends \Neomerx\JsonApi\Representation\FieldSetFilter
{
    /**
     * @param string   $type
     * @param iterable $fields
     *
     * @return iterable
     */
    protected function filterFields(string $type, iterable $fields): iterable
    {
        if ($this->hasFilter($type) === false) {
            foreach ($fields as $name => $value) {
                yield $name => $this->resolveValue($value);
            }

            return;
        }

        $allowedFields = $this->getAllowedFields($type);
        foreach ($fields as $name => $value) {
            if (isset($allowedFields[$name]) === true) {
                yield $name => $this->resolveValue($value);
            }
        }
    }

    /**
     * Resolves a given by either calling it if it's a callable. Otherwise
     * just return the value itself.
     *
     * @param mixed $value
     * @return mixed
     */
    private function resolveValue($value)
    {
        if ($value instanceof \Closure) {
            return $value();
        }

        return $value;
    }
}
