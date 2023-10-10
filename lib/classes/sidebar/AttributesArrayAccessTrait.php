<?php
trait AttributesArrayAccessTrait
{
    public $attributes = [];

    /**
     * @todo Add bool return type when Stud.IP requires PHP8 minimal
     */
    #[ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * @param $offset
     * @return mixed
     *
     * @todo Add mixed return type when Stud.IP requires PHP8 minimal
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->attributes[$offset];
    }

    /**
     * @todo Add void return type when Stud.IP requires PHP8 minimal
     */
    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    /**
     * @todo Add void return type when Stud.IP requires PHP8 minimal
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }
}
