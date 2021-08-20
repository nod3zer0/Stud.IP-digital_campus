<?php

namespace JsonApi\JsonApiIntegration;

use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Neomerx\JsonApi\Contracts\Parser\EditableContextInterface;
use Neomerx\JsonApi\Exceptions\InvalidArgumentException;
use Neomerx\JsonApi\Parser\Parser as NeomerxParser;
use SimpleORMap;
use function Neomerx\JsonApi\I18n\format as _;

/**
 * Eine Instanz von Neomerx\JsonApi\Encoder\Parser\Parser wird
 * benötigt, um Werte, die an den JSON-API-Encoder gehen, zu
 * analysieren und entsprechned weiter zu verarbeiten. Unter anderem
 * wird darin auch die Unterscheidung getroffen, ob Werte, die an den
 * JSON-API-Encoder gehen, Collections sind oder nicht.
 *
 * Bei dieser Analyse werden sinnvollerweise alle Werte, die das
 * PHP-Interface \IteratorAggregate implementieren, als Collections
 * behandelt. Da aber die Stud.IP-Klasse \SimpleORMap
 * ungewöhnlicherweise ebenfalls dieses Interface implementiert, muss
 * hier eine Sonderbehandlung stattfinden.
 *
 * Dazu wird die Methode
 * Neomerx\JsonApi\Encoder\Parser\Parser::analyzeCurrentData so
 * überschrieben, dass Instanzen von \SimpleORMap nicht als
 * Collections gelten.
 *
 * @see Neomerx\JsonApi\Parser\Parser
 * @see \SimpleORMap
 */
class Parser extends NeomerxParser
{
    /**
     * @var SchemaContainerInterface
     */
    private $schemaContainer;

    /**
     * As `$schemaContainer` is private in \Neomerx\JsonApi\Parser\Parser it has
     * to be stored again in this subclass.
     *
     * @param FactoryInterface         $factory
     * @param SchemaContainerInterface $container
     * @param EditableContextInterface $context
     */
    public function __construct(
        FactoryInterface $factory,
        SchemaContainerInterface $container,
        EditableContextInterface $context
    ) {
        $this->schemaContainer = $container;

        parent::__construct($factory, $container, $context);
    }

    /**
     * Show better error messages using instances of subclasses of \SimpleORMap
     * without a Schema.
     *
     * @inheritdoc
     */
    public function parse($data, array $paths = []): iterable
    {
        \assert(\is_array($data) === true || \is_object($data) === true || $data === null);

        if ($data instanceof SimpleORMap && $this->schemaContainer->hasSchema($data) !== true) {
            throw new InvalidArgumentException(_(static::MSG_NO_SCHEMA_FOUND, \get_class($data)));
        }

        return parent::parse($data, $paths);
    }
}
