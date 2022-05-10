<?php
/**
 * file_lock.php
 * Simple lock mechanism on a file basis.
 *
 * With the help of this class you can manage persistent locks. Locks are
 * stored in files and potential additional data is stored as json.
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option)
 * any later version.
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @copyright 2013 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 * @since     2.4
 */

class FileLock
{
    protected $file;

    /**
     * Constructs a new lock object with the provided id.
     *
     * @param String $id Identifier of the lock
     */
    public function __construct($id)
    {
        $this->file = fopen("{$GLOBALS['TMP_PATH']}/$id.json", 'c+');

        if (!$this->file) {
            throw new RuntimeException('failed to create lock file.');
        }
    }
    
    /**
     * Try to aquire a file lock. The provided lock information will
     * be stored with the lock. If the lock cannot be aquired, the
     * lock information in $data is updated from the lock file.
     *
     * @param array $data additional data to be stored with the lock
     * @return boolean true on success or false on failure
     */
    public function tryLock(&$data = [])
    {
        rewind($this->file);

        if (flock($this->file, LOCK_EX | LOCK_NB)) {
            ftruncate($this->file, 0);
            fwrite($this->file, json_encode($data));
            fflush($this->file);

            return true;
        } else {
            $json = stream_get_contents($this->file);
            $data = json_decode($json, true);

            return false;
        }
    }

    /**
     * Releases a previously obtained lock
     *
     * @return boolean true on success or false on failure
     */
    public function release()
    {
        return flock($this->file, LOCK_UN);
    }
}
