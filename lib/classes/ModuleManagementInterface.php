<?php
/**
 * This interface ensures that all objects of ModuleManagementModel have
 * the same constructor signature. Otherwise, we can not guarantee that the
 * use of "new static()" in ModuleManagement code will always do the right
 * things.
 *
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
interface ModuleManagementInterface
{
    public function __construct($id = null);
}
