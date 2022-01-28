<?php
/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class ConsultationMailer
{
    private static $messaging = null;

    /**
     * Returns a messaging object.
     *
     * @return messaging object
     */
    private static function getMessaging()
    {
        if (self::$messaging === null) {
            self::$messaging = new messaging();
        }
        return self::$messaging;
    }

    /**
     * Sends a consultation information message.
     *
     * @param User|null         $sender  Sender
     * @param User             $user    Recipient
     * @param ConsultationSlot $slot    Slot in question
     * @param string           $subject Subject of the message
     * @param string           $reason  Reason for a booking or cancelation
     * @param User             $sender  Sender of the message
     */
    public static function sendMessage(?User $sender, User $user, ConsultationBooking $booking, string $subject, string $reason = '')
    {
        // Don't send message if user doesn't want it
        if (!UserConfig::get($user->id)->CONSULTATION_SEND_MESSAGES) {
            return;
        }

        setTempLanguage($user->id);

        $message = $GLOBALS['template_factory']->open('consultations/mail.php')->render([
            'user'   => $booking->user,
            'slot'   => $booking->slot,
            'reason' => $reason ?: _('Kein Grund angegeben'),
        ]);

        if ($sender === null) {
            messaging::sendSystemMessage($user, $subject, $message);
        } else {
            $messaging = new messaging();
            $messaging->insert_message($message, $user->username, $sender->id, '', '', '', '', $subject);
        }

        restoreLanguage();
    }

    /**
     * Send a booking information message to the teacher of the booked slot.
     *
     * @param User|null            $sender
     * @param ConsultationBooking $booking The booking
     */
    public static function sendBookingMessageToResponsibilities(?User $sender, ConsultationBooking $booking)
    {
        foreach ($booking->slot->block->responsible_persons as $user) {
            if ($user->id === $GLOBALS['user']->id) {
                continue;
            }

            self::sendMessage(
                $sender,
                $user,
                $booking,
                sprintf(_('Termin von %s zugesagt'), $booking->user->getFullName()), $booking->reason
            );
        }
    }

    /**
     * Send a booking information message to the user of the booked slot.
     *
     * @param User|null            $sender
     * @param  ConsultationBooking $booking The booking
     */
    public static function sendBookingMessageToUser(?User $sender, ConsultationBooking $booking)
    {
        self::sendMessage(
            $sender,
            $booking->user,
            $booking,
            sprintf(_('Termin bei %s zugesagt'), $booking->slot->block->range_display), $booking->reason
        );
    }

    /**
     * Send an information message about a changed reason to a user of the
     * booked slot.
     *
     * @param User                 $sender   The sender of the message
     * @param ConsultationBooking $booking  The booking
     * @param User                $receiver The receiver of the message
     */
    public static function sendReasonMessage(?User $sender, ConsultationBooking $booking, User $receiver)
    {
        self::sendMessage(
            $sender,
            $receiver,
            $booking,
            sprintf(_('Grund des Termins bei %s bearbeitet'), $booking->slot->block->range_display), $booking->reason
        );
    }

    /**
     * Send a cancelation message to the teacher of the booked slot.
     *
     * @param User|null            $sender
     * @param  ConsultationBooking $booking The booking
     * @param String               $reason  Reason of the cancelation
     */
    public static function sendCancelMessageToResponsibilities(?User $sender, ConsultationBooking $booking, string $reason = '')
    {
        foreach ($booking->slot->block->responsible_persons as $user) {
            if ($user->id === $GLOBALS['user']->id) {
                continue;
            }

            self::sendMessage(
                $sender,
                $user,
                $booking,
                sprintf(_('Termin von %s abgesagt'), $booking->user->getFullName()), trim($reason)
            );
        }
    }

    /**
     * Send a cancelation message to the user of the booked slot.
     *
     * @param User|null            $sender
     * @param  ConsultationBooking $booking The booking
     * @param String               $reason  Reason of the cancelation
     */
    public static function sendCancelMessageToUser(?User $sender, ConsultationBooking $booking, string $reason)
    {
        self::sendMessage(
            $sender,
            $booking->user,
            $booking,
            sprintf(_('Termin bei %s abgesagt'), $booking->slot->block->range_display), trim($reason)
        );
    }
}
