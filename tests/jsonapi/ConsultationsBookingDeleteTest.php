<?php
use JsonApi\Routes\Consultations\BookingsDelete;

require_once __DIR__ . '/ConsultationHelper.php';

class ConsultationsBookingDeleteTest extends Codeception\Test\Unit
{
    use ConsultationHelper;

    public function testDeleteBookingWithoutReason(): void
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $range = User::find($credentials['id']);

        $block = $this->createBlockWithSlotsForRange($range);
        $slot = $this->getSlotFromBlock($block);
        $booking = $this->createBookingForSlot(
            $credentials,
            $slot,
            $this->getUserForCredentials($this->tester->getCredentialsForTestAutor())
        );

        $this->sendMockRequest(
            '/consultation-bookings/{id}',
            BookingsDelete::class,
            $credentials,
            ['id' => $booking->id],
            [
                'considered_successful' => [204],
                'method' => 'DELETE',
            ]
        );
    }

    public function testDeleteBookingWithReason(): void
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $range = User::find($credentials['id']);

        $block = $this->createBlockWithSlotsForRange($range);
        $slot = $this->getSlotFromBlock($block);
        $booking = $this->createBookingForSlot(
            $credentials,
            $slot,
            $this->getUserForCredentials($this->tester->getCredentialsForTestAutor())
        );

        $this->sendMockRequest(
            '/consultation-bookings/{id}',
            BookingsDelete::class,
            $credentials,
            ['id' => $booking->id],
            [
                'considered_successful' => [204],
                'method' => 'DELETE',
                'json_body' => [
                    'data' => [
                        'attributes' => [
                            'reason' => self::$BOOKING_DATA['reason'],
                        ]
                    ],
                ],
            ]
        );
    }
}
