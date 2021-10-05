<?php

namespace JsonApi\Schemas;

use JsonApi\Errors\InternalServerError;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Neomerx\JsonApi\Schema\BaseSchema;

abstract class SchemaProvider extends BaseSchema
{
    /** @var SchemaContainerInterface */
    protected $schemaContainer;

    /** @var ?\User */
    protected $currentUser;

    public function __construct(FactoryInterface $factory, SchemaContainerInterface $schemaContainer, ?\User $user)
    {
        $this->schemaContainer = $schemaContainer;
        $this->currentUser = $user;

        parent::__construct($factory);
    }

    const TYPE = '';

    public function getType(): string
    {
        return static::TYPE;
    }

    /**
     * @inheritdoc
     */
    public function isAddSelfLinkInRelationshipByDefault(string $relationshipName): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isAddRelatedLinkInRelationshipByDefault(string $relationshipName): bool
    {
        return false;
    }

    /**
     * @param mixed $resource
     */
    public function createLinkToResource($resource): LinkInterface
    {
        if (!$this->schemaContainer->hasSchema($resource)) {
            throw new InternalServerError('Cannot create links to objects without schema.');
        }

        return $this->schemaContainer->getSchema($resource)->getSelfLink($resource);
    }

    /**
     * @param ContextInterface $context
     * @param string $key
     *
     * @return bool true, if the given relationship should be included in the response
     */
    public function shouldInclude(ContextInterface $context, string $key): bool
    {
        $path = $context->getPosition()->getLevel() ? $context->getPosition()->getPath() . '.' : '';

        return in_array($path . $key, $context->getIncludePaths());
    }
}
