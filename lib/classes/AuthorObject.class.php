<?php
# Lifter002: TEST
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// AuthorObject.class.php
//
// Class to provide basic properties of an object in Stud.IP
//
// Copyright (c) 2003 Alexander Willner <mail@AlexanderWillner.de>
// +--------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +--------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +--------------------------------------------------------------------------+


define("ERROR_NORMAL", "1");
define("ERROR_CRITICAL", "8");


/**
 * AuthorObject.class.php
 *
 * Class to provide basic properties of an object in Stud.IP
 *
 * @author      Alexander Willner <mail@alexanderwillner.de>
 * @copyright   2003 Stud.IP-Project
 * @access      public
 * @package     studip_core
 * @modulegroup core
 */
class AuthorObject
{

    /**
     * Holds the code and description of an internal error
     * @access   private
     * @var      array $errorArray
     */
    public $errorArray;

    /**
     * Holds the type of object. See INSTANCEOF_*
     * @access   private
     * @var      string $instanceof
     */
    public $instanceof;

    /**
     * Constructor
     * @access   public
     */
    public function __construct()
    {
        $this->instanceof = 'AuthorObject';
        $this->errorArray = [];
    }

    /**
     * Gets the type of object
     * @access  public
     * @return  string The type of object. See INSTANCEOF_*
     */
    public function x_instanceof()
    {
        return $this->instanceof;
    }

    /**
     * Gives the internal errorcode
     * @access public
     * @return boolean True if an error exists
     */
    public function isError()
    {
        return count($this->errorArray) > 0;
    }

    /**
     * Gives the codes and descriptions of the internal errors
     * @access  public
     * @return  array  The errors as an Array like "1" => "Could not open DB"
     */
    public function getErrors()
    {
        return $this->errorArray;
    }

    /**
     * Resets the errorcodes and descriptions
     * @access public
     */
    public function resetErrors()
    {
        $this->errorArray = [];
    }

    /**
     * Sets the errorcode (internal)
     * @access  public
     * @param integer $errcode The code of the error
     * @param string $errstring The description of the error
     */
    public function throwError($errcode, $errstring)
    {
        if (!is_array($this->errorArray)) {
            $this->errorArray = [];
        }

        $this->errorArray [] = [
            'code'   => $errcode,
            'string' => $errstring,
        ];
    }

    /**
     * Sets the errorcode from other classes (internal)
     * @access  private
     * @param object $class The class with the error
     */
    public function throwErrorFromClass(AuthorObject $class)
    {
        $this->errorArray = $class->getErrors();
        $class->resetErrors();
    }
}

