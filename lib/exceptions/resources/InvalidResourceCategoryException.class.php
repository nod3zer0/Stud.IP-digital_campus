<?php

/**
 * InvalidResourceCategoryException.class.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * This exception is thrown when a resource category does not exist
 * or a resource is used with a resource category that does not match
 * or when a resource category cannot be created due to invalid data.
 */
class InvalidResourceCategoryException extends InvalidArgumentException
{

}
