<?php
namespace RESTAPI;

/**
 * @author     <mlunzena@uos.de>
 * @license    GPL 2 or later
 * @since      Stud.IP 3.0
 * @deprecated Since Stud.IP 5.0. Will be removed in Stud.IP 6.0.
 */
class RouterHalt extends \Exception
{
    public $response;

    public function __construct($response)
    {
        parent::__construct();
        $this->response = $response;
    }
}
