<?php
/**
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license   GPL2 or any later version
 * @since     4.5
 *
 * @property int $id alias column for mention_id
 * @property int $mention_id database column
 * @property string $thread_id database column
 * @property string $user_id database column
 * @property int $external_contact database column
 * @property int $mkdate database column
 * @property BlubberThread $thread belongs_to BlubberThread
 * @property User $user belongs_to User
 */

class BlubberMention extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'blubber_mentions';

        $config['belongs_to']['thread'] = [
            'class_name'        => BlubberThread::class,
            'foreign_key'       => 'thread_id',
            'assoc_foreign_key' => 'thread_id',
        ];
        $config['belongs_to']['user'] = [
            'class_name'        => User::class,
            'foreign_key'       => 'user_id',
            'assoc_foreign_key' => 'user_id',
        ];

        parent::configure($config);
    }
}
