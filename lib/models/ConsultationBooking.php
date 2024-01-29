<?php
/**
 * Representation of a user's booking of a consultation slots.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.3
 *
 * @property int $id alias column for booking_id
 * @property int $booking_id database column
 * @property int $slot_id database column
 * @property string $user_id database column
 * @property string|null $reason database column
 * @property string|null $student_event_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property ConsultationSlot $slot belongs_to ConsultationSlot
 * @property User $user belongs_to User
 * @property CalendarDate $event has_one CalendarDate
 */
class ConsultationBooking extends SimpleORMap implements PrivacyObject
{
    /**
     * Configures the model.
     * @param array  $config Configuration
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'consultation_bookings';

        $config['belongs_to']['slot'] = [
            'class_name'  => ConsultationSlot::class,
            'foreign_key' => 'slot_id',
        ];
        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id',
        ];
        $config['has_one']['event'] = [
            'class_name'        => CalendarDate::class,
            'foreign_key'       => 'student_event_id',
            'assoc_foreign_key' => 'id',
            'on_delete'         => 'delete',
        ];

        // Create student event
        $config['registered_callbacks']['before_create'][] = function (ConsultationBooking $booking) {
            setTempLanguage($booking->user_id);

            $event = $booking->slot->createEvent($booking->user);
            $event->category = 1;
            $event->title = sprintf(
                _('Termin bei %s'),
                $booking->slot->block->range->getFullName()
            );
            $event->description = $booking->reason;
            $event->store();

            restoreLanguage();

            $booking->student_event_id = $event->id;
        };

        $config['registered_callbacks']['after_create'][] = function (ConsultationBooking $booking) {
            ConsultationMailer::sendBookingMessageToUser($GLOBALS['user']->getAuthenticatedUser(), $booking);
            ConsultationMailer::sendBookingMessageToResponsibilities($GLOBALS['user']->getAuthenticatedUser(), $booking);
        };

        $config['registered_callbacks']['before_store'][] = function (ConsultationBooking $booking) {
            if (!$booking->isNew() && $booking->isFieldDirty('reason')) {
                if ($GLOBALS['user']->id !== $booking->user_id) {
                    ConsultationMailer::sendReasonMessage($GLOBALS['user']->getAuthenticatedUser(), $booking, $booking->user);
                }

                $responsible_persons = $booking->slot->block->responsible_persons;
                foreach ($responsible_persons as $user) {
                    if ($GLOBALS['user']->id !== $user->id) {
                        ConsultationMailer::sendReasonMessage($GLOBALS['user']->getAuthenticatedUser(), $booking, $user);
                    }
                }
            }
        };

        $config['registered_callbacks']['after_store'][] = function (ConsultationBooking $booking) {
            if ($booking->event) {
                $booking->event->description = $booking->reason;
                $booking->event->store();
            }

            $booking->slot->updateEvents();
        };

        $config['registered_callbacks']['after_delete'][] = function (ConsultationBooking $booking) {
            $booking->slot->updateEvents();
        };

        parent::configure($config);
    }

    /**
     * Returns whether a user may create a booking for the given range.
     *
     * @param User $user
     * @return bool
     */
    public static function userMayCreateBookingForRange(\Range $range, \User $user): bool
    {
        if (!($range instanceof \User)) {
            return true;
        }

        $allowed_perms = ['user', 'autor', 'tutor'];
        if (Config::get()->CONSULTATION_ALLOW_DOCENTS_RESERVING) {
            $allowed_perms[] = 'dozent';
        }

        return in_array($user->perms, $allowed_perms);
    }

    public function cancel($reason = '')
    {
        if ($GLOBALS['user']->id !== $this->user_id) {
            ConsultationMailer::sendCancelMessageToUser($GLOBALS['user']->getAuthenticatedUser(), $this, $reason);
        }

        ConsultationMailer::sendCancelMessageToResponsibilities($GLOBALS['user']->getAuthenticatedUser(), $this, $reason);

        return $this->delete() ? 1 : 0;
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $bookings = self::findByUser_id($storage->user_id);
        if ($bookings) {
            $storage->addTabularData(
                _('Terminbelegungen'),
                'consultation_bookings',
                array_map(function ($booking) {
                    return $booking->toRawArray();
                }, $bookings)
            );
        }
    }
}
