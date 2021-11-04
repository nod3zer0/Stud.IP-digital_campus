<?php
/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 5.1
 *
 * @property int block_id database column
 * @property int id alias column for block_id
 * @property string range_id database column
 * @property string range_type database column
 * @property int mkdate database column
 */
class ConsultationResponsibility extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'consultation_responsibilities';

        $config['belongs_to']['block'] = [
            'class_name'  => ConsultationBlock::class,
            'foreign_key' => 'block_id',
        ];

        parent::configure($config);
    }

    /**
     * Returns the name of the associated responsibility.
     *
     * @return string
     * @throws Exception
     */
    public function getName()
    {
        if ($this->range_type === 'user') {
            return User::find($this->range_id)->getFullName();
        }
        if ($this->range_type === 'statusgroup') {
            return Statusgruppen::find($this->range_id)->getName();
        }
        if ($this->range_type === 'institute') {
            return Institute::find($this->range_id)->getFullName();
        }
        throw new Exception('Unknown range type');
    }

    /**
     * Returns an url to the associated responsibility.
     *
     * @return string
     * @throws Exception
     */
    public function getURL()
    {
        if ($this->range_type === 'user') {
            $user = User::find($this->range_id);
            return URLHelper::getURL('dispatch.php/profile', ['username' => $user->username], true);
        }
        // TODO: Check if staff tab is activated and link to that
        if ($this->range_type === 'statusgroup') {
            $institute = Statusgruppen::find($this->range_id)->institute;
            return URLHelper::getURL('dispatch.php/institute/overview', ['auswahl' => $institute->id], true);
        }
        if ($this->range_type === 'institute') {
            return URLHelper::getURL('dispatch.php/institute/overview', ['auswahl' => $this->range_id], true);
        }
        throw new Exception('Unknown range type');
    }

    /**
     * Returns all users belonging to the associated responsibility.
     *
     * @return array
     * @throws Exception
     */
    public function getUsers()
    {
        if ($this->range_type === 'user') {
            return [User::find($this->range_id)];
        }
        if ($this->range_type === 'statusgroup') {
            $group = Statusgruppen::find($this->range_id);
            return self::getStatusgroupResponsibilities($group);
        }
        if ($this->range_type === 'institute') {
            $institute = Institute::find($this->range_id);
            return self::getInstituteResponsibilites($institute);
        }
        throw new Exception('Unknown range type');
    }

    /**
     * Returns all responsible users for a course.
     *
     * @param Course $course
     * @return array
     */
    public static function getCourseResponsibilities(Course $course)
    {
        return $course->getMembersWithStatus('tutor dozent', true)->pluck('user');
    }

    /**
     * Returns all responsible users for a status group.
     *
     * @param Statusgruppen $group
     * @return array
     */
    public static function getStatusgroupResponsibilities(Statusgruppen $group)
    {
        return $group->members->pluck('user');
    }

    /**
     * Returns all responsible users for an institute.
     *
     * @param Institute $institute
     * @return array
     */
    public static function getInstituteResponsibilites(Institute $institute)
    {
        return $institute->members->filter(function (InstituteMember $member) {
            return in_array($member->inst_perms, ['tutor', 'dozent']);
        })->pluck('user');
    }
}
