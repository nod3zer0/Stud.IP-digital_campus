<?php

namespace JsonApi\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class DummyNavigation extends \Navigation implements \ArrayAccess
{
    /**
     * Return the list of subnavigation items of this object.
     */
    public function getSubNavigation()
    {
        return $this;
    }

    /**
     * ArrayAccess: Check whether the given offset exists.
     */
    public function offsetExists($offset)
    {
        return true;
    }

    /**
     * ArrayAccess: Get the value at the given offset.
     */
    public function offsetGet($offset)
    {
        return $this;
    }

    /**
     * ArrayAccess: Set the value at the given offset.
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * ArrayAccess: Delete the value at the given offset.
     */
    public function offsetUnset($offset)
    {
    }

    /**
     * IteratorAggregate: Create interator for request parameters.
     */
    public function getIterator()
    {
        return new \ArrayIterator();
    }
}

class StudipMockNavigation
{
    /**
     * @param Request                 $request das PSR-7 Request-Objekt
     * @param RequestHandlerInterface $handler das PSR-7 Response-Objekt
     *
     * @return ResponseInterface die neue Response
     */
    public function __invoke(Request $request, RequestHandler $handler)
    {
        \Navigation::setRootNavigation(new DummyNavigation('stuff'));

        return $handler->handle($request);
    }
}
