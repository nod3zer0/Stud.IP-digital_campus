<?php

namespace JsonApi\JsonApiIntegration;

use Neomerx\JsonApi\Contracts\Parser\EditableContextInterface;
use Neomerx\JsonApi\Contracts\Parser\ParserInterface;
use Neomerx\JsonApi\Contracts\Representation\FieldSetFilterInterface;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Neomerx\JsonApi\Factories\Factory as NeomerxFactory;
use Neomerx\JsonApi\Schema\Link;

/**
 * Die "normale" \Neomerx\JsonApi\Factories\Factory stellt in
 * Factory::createParser einen \Neomerx\JsonApi\Encoder\Parser\Parser
 * her. Dieser hat aber Probleme mit Instanzen von \SimpleORMap,
 * sodass diese Factory einen speziellen EncoderParser herstellt, der
 * diese Probleme nicht hat.
 *
 * @see \Neomerx\JsonApi\Factories\Factory
 * @see \Neomerx\JsonApi\Encoder\Parser\Parser
 * @see EncoderParser
 */
class Factory extends NeomerxFactory
{
    /**
     * @inheritdoc
     */
    public function createFieldSetFilter(array $fieldSets): FieldSetFilterInterface
    {
        return new FieldsetFilter($fieldSets);
    }

    /**
     * @inheritdoc
     */
    public function createParser(
        SchemaContainerInterface $container,
        EditableContextInterface $context
    ): ParserInterface {
        return new Parser($this, $container, $context);
    }

    /**
     * @inheritdoc
     */
    public function createSchemaContainer(iterable $schemas): SchemaContainerInterface
    {
        return new SchemaContainer($this, $schemas);
    }
}
