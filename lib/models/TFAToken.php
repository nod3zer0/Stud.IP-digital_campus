<?php
/**
 * Model for a two factor authentication token stored in the database.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.4
 *
 * @property array $id alias for pk
 * @property string $user_id database column
 * @property string $token database column
 * @property int $mkdate database column
 */
class TFAToken extends SimpleORMap
{
    /**
     * Configures the model.
     *
     * @param  array  $config Configuration
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'users_tfa_tokens';

        parent::configure($config);
    }
}
