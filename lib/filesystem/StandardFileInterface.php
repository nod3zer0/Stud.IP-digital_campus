<?php
interface StandardFileInterface
{
    /**
     * StandardFile constructor.
     * @param $fileref
     * @param null $file : (optional) Is set if fileref and file are both new and not connected with
     *                     each other in the database.
     */
    public function __construct($fileref, $file = null);
}
