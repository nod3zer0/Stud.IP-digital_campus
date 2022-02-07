<?php
/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class Filter
{
    private
        $start_date,
        $end_date,
        $type,
        $verb,
        $objectType,
        $objectId,
        $context,
        $contextId;

    /**
     *
     * @param string $start_date
     */
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }

    /**
     *
     * @return string
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     *
     * @param string $end_date
     */
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
    }

    /**
     *
     * @return string
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @return string
     */
    public function getVerb()
    {
        return $this->verb;
    }

    /**
     *
     * @param string $verb
     */
    public function setVerb($verb)
    {
        $this->verb = $verb;
    }

    /**
     *
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     *
     * @param string $objectType
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }

    /**
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     *
     * @param string $objectId
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
    }

    /**
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     *
     * @param string $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     *
     * @return string
     */
    public function getContextId()
    {
        return $this->contextId;
    }

    /**
     *
     * @param string $contextId
     */
    public function setContextId($contextId)
    {
        $this->contextId = $contextId;
    }
}
