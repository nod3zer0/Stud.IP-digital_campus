<?php

namespace JsonApi\JsonApiIntegration;

use Closure;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Exceptions\InvalidArgumentException;
use function Neomerx\JsonApi\I18n\format as _;
use Neomerx\JsonApi\Schema\SchemaContainer as NeomerxSchemaContainer;

class SchemaContainer extends NeomerxSchemaContainer
{
    /**
     * The original SchemaContainer does not like mappings of interfaces to schemas.
     * So this method now allows classes *and* interfaces.
     *
     * @inheritdoc
     */
    public function register(string $type, $schema): void
    {
        if (true === empty($type) || (false === \class_exists($type) && false === \interface_exists($type))) {
            throw new InvalidArgumentException(_(static::MSG_INVALID_MODEL_TYPE));
        }

        $isOk =
            (true === \is_string($schema) &&
                false === empty($schema) &&
                true === \class_exists($schema) &&
                true === \in_array(SchemaInterface::class, \class_implements($schema))) ||
            \is_callable($schema) ||
            $schema instanceof SchemaInterface;
        if (false === $isOk) {
            throw new InvalidArgumentException(_(static::MSG_INVALID_SCHEME, $type));
        }

        if (true === $this->hasProviderMapping($type)) {
            throw new InvalidArgumentException(_(static::MSG_TYPE_REUSE_FORBIDDEN, $type));
        }

        if ($schema instanceof SchemaInterface) {
            $this->setProviderMapping($type, \get_class($schema));
            $this->setResourceToJsonTypeMapping($schema->getType(), $type);
            $this->setCreatedProvider($type, $schema);
        } else {
            $this->setProviderMapping($type, $schema);
        }
    }

    /**
     * @param callable $callable
     *
     * @return SchemaInterface
     */
    protected function createSchemaFromCallable(callable $callable): SchemaInterface
    {
        $schema = \call_user_func($callable, $this);

        return $schema;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function hasProviderMapping(string $type): bool
    {
        if (parent::hasProviderMapping($type)) {
            return true;
        }

        foreach ($this->getParentClassesAndInterfaces($type) as $class) {
            if (parent::hasProviderMapping($class)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    protected function getProviderMapping(string $type)
    {
        if (parent::hasProviderMapping($type)) {
            return parent::getProviderMapping($type);
        }

        foreach ($this->getParentClassesAndInterfaces($type) as $class) {
            if (parent::hasProviderMapping($class)) {
                return parent::getProviderMapping($class);
            }
        }
        throw new InvalidArgumentException(_('Cannot find schema for type `%s`', $type));
    }

    private function getParentClassesAndInterfaces(string $type): array
    {
        return class_exists($type) ? @class_parents($type) + @class_implements($type) : [];
    }
}
