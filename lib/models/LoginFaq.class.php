<?php
/**
 * LoginFaq.class.php
 * model class for table login_faq
 *
 *
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Michaela Brückner <brueckner@data-quest.de>
 * @copyright   2023 data-quest
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       5.5
*/
class LoginFaq extends SimpleORMap
{
    /**
     * @param array $config
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'login_faq';

    $config['i18n'] = ['title', 'description'];

        parent::configure($config);
    }
}
