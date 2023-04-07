<?php
/**
 * SimpleORMapCollection.class.php
 * simple object-relational mapping
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2012 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @extends SimpleCollection<SimpleORMap>
 *
 * @template T of SimpleORMap
 */
class SimpleORMapCollection extends SimpleCollection
{
    /**
     * @var int Exception error code denoting a wrong type of objects.
     */
    const WRONG_OBJECT_TYPE = 1;

    /**
     * @var int Exception error code denoting that an object of this `id` already exists.
     */
    const OBJECT_EXISTS = 2;

    /**
     * the record object this collection belongs to
     *
     * @var ?SimpleORMap
     */
    protected $related_record;

    /**
     * relation options
     * @var array
     */
    protected $relation_options = [];

    /**
     * creates a collection from an array of objects
     * all objects should be of the same type
     *
     * @throws InvalidArgumentException if first entry is not SimpleOrMap
     * @param T[] $data array with SimpleORMap objects
     * @param bool $strict check every element for correct type and unique pk
     * @return SimpleORMapCollection<T>
     */
    public static function createFromArray(array $data, $strict = true)
    {
        $ret = new SimpleORMapCollection();
        if (count($data)) {
            $first = current($data);
            if ($first instanceof SimpleORMap) {
                $ret->setClassName(get_class($first));
                if ($strict) {
                    foreach ($data as $one) {
                        $ret[] = $one;
                    }
                } else {
                    $ret->exchangeArray($data);
                }
            } else {
                throw new InvalidArgumentException('This collection only accepts objects derived from SimpleORMap', self::WRONG_OBJECT_TYPE);
            }
        }
        return $ret;
    }

    /**
     * Constructor
     *
     * @param ?Closure         $finder  callable to fill collection
     * @param ?array           $options relationship options
     * @param SimpleORMap|null $record  related record
     */
    public function __construct(Closure $finder = null, array $options = null, SimpleORMap $record = null)
    {
        $this->relation_options = $options;
        $this->related_record = $record;
        parent::__construct($finder === null ? [] : $finder);
    }

    /**
     * Sets the value at the specified index
     * checks if the value is an object of specified class
     *
     * @see ArrayObject::offsetSet()
     * @throws InvalidArgumentException if the given model does not fit (wrong type or id)
     *
     * @todo Add void return type when Stud.IP requires PHP8 minimal
     */
    #[ReturnTypeWillChange]
    public function offsetSet($index, $newval)
    {
        if (!is_null($index)) {
            $index = (int)$index;
        }
        if (!is_a($newval, $this->getClassName())) {
            throw new InvalidArgumentException('This collection only accepts objects of type: ' .  $this->getClassName(), self::WRONG_OBJECT_TYPE);
        }
        if ($this->related_record && $this->relation_options['type'] === 'has_many') {
            $foreign_key_value = call_user_func($this->relation_options['assoc_func_params_func'], $this->related_record);
            call_user_func($this->relation_options['assoc_foreign_key_setter'], $newval, $foreign_key_value);
        }
        if ($newval->id !== null) {
            $exists = $this->find($newval->id);
            if ($exists) {
                throw new InvalidArgumentException('Element could not be appended, element with id: ' . $exists->id . ' is in the way', self::OBJECT_EXISTS);
            }
        }
        parent::offsetSet($index, $newval);
    }

    /**
     * sets the allowed class name
     * @param class-string $class_name
     * @return void
     */
    public function setClassName($class_name)
    {
        $this->relation_options['class_name'] = strtolower($class_name);
        $this->deleted->relation_options['class_name'] = strtolower($class_name);
    }

    /**
     * sets the related record
     *
     * @param SimpleORMap $record
     * @return void
     */
    public function setRelatedRecord(SimpleORMap $record)
    {
        $this->related_record = $record;
    }

    /**
     * gets the allowed classname
     *
     * @return string
     */
    public function getClassName()
    {
        return strtolower($this->relation_options['class_name']);
    }

    /**
     * reloads the elements of the collection
     * by calling the finder function
     *
     * @throws InvalidArgumentException
     * @return ?int number of records after refresh
     */
    public function refresh()
    {
        if (is_callable($this->finder)) {
            $data = call_user_func($this->finder, $this->related_record);
            foreach ($data as $one) {
                if (!is_a($one, $this->getClassName())) {
                    throw new InvalidArgumentException('This collection only accepts objects of type: ' .  $this->getClassName(), self::WRONG_OBJECT_TYPE);
                }
            }
            $this->exchangeArray($data);
            $this->deleted->exchangeArray([]);
            return $this->last_count = $this->count();
        }

        return null;
    }

    /**
     * returns element with given primary key value
     *
     * @param string $value primary key value to search for
     * @return ?T
     */
    public function find($value)
    {
        return $this->findOneBy('id', $value);
    }

    /**
     * returns the collection as grouped array
     * first param is the column to group by, it becomes the key in
     * the resulting array, default is pk. Limit returned fields with second param
     * The grouped entries can optoionally go through the given
     * callback. If no callback is provided, only the first grouped
     * entry is returned, suitable for grouping by unique column
     *
     * @param string $group_by the column to group by, pk if ommitted
     * @param mixed $only_these_fields limit returned fields
     * @param ?callable $group_func closure to aggregate grouped entries
     * @return array assoc array
     */
    public function toGroupedArray($group_by = 'id', $only_these_fields = null, callable $group_func = null)
    {
        $result = [];
        foreach ($this as $record) {
            $key = $record->getValue($group_by);
            if (is_array($key)) {
                $key = join('_', $key);
            }
            $result[$key][] = $record->toArray($only_these_fields);
        }
        if ($group_func === null) {
            $group_func = 'current';
        }
        return array_map($group_func, $result);
    }

    /**
     * mark element(s) for deletion
     * element(s) with given primary key are moved to
     * internal deleted collection
     *
     * @param string $id primary key of element
     * @return int number of unsetted elements
     */
    public function unsetByPk($id)
    {
        return $this->unsetBy('id', $id);
    }

    /**
     * merge in another collection, elements must be of
     * the same type, if an element already exists it is
     * replaced or ignored depending on second param
     *
     * @param SimpleORMapCollection $a_collection
     * @param string $mode 'replace' or 'ignore'
     * @return void
     */
    public function merge(SimpleCollection $a_collection, string $mode = 'ignore')
    {
        $mode = func_get_arg(1);
        foreach ($a_collection as $element) {
            try {
                /**
                 * @throws InvalidArgumentException
                 * @see SimpleORMapCollection::offsetSet()
                 */
                $this[] = $element;
            } catch (InvalidArgumentException $e) {
                if ($e->getCode() === self::OBJECT_EXISTS) {
                    if ($mode === 'replace') {
                        $this->unsetByPk($element->id);
                        $this[] = $element;
                    } // else $mode means 'ignore'
                } else {
                    throw $e;
                }
            }
        }
        $this->storage = array_values($this->storage);
    }
}
