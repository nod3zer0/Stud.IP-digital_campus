<?php
use JsonApi\Routes\Consultations\BookingsCreate;
use JsonApi\Schemas\ConsultationBooking as Schema;
use JsonApi\Schemas\User as UserSchema;
use JsonApi\Schemas\ConsultationSlot as SlotSchema;
use WoohooLabs\Yang\JsonApi\Response\JsonApiResponse;

require_once __DIR__ . '/ConsultationHelper.php';

// TODO: Test locked blocks
class ConsultationsBookingCreateTest extends Codeception\Test\Unit
{
    use ConsultationHelper;

    public function testAutorMayCreateBooking(): void
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $range = User::find($credentials['id']);

        $block = $this->createBlockWithSlotsForRange($range);
        $slot = $this->getSlotFromBlock($block);

        $this->createBooking(
            $credentials,
            $slot,
            $this->tester->getCredentialsForTestAutor()['id'],
            [201]
        );
    }

    public function testAutorMayCreateNotCreateBookingDueToLock(): void
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $range = User::find($credentials['id']);

        $block = $this->createBlockWithSlotsForRange($range, ['lock_time' => 2]);
        $slot = $this->getSlotFromBlock($block);

        $response = $this->createBooking(
            $credentials,
            $slot,
            $this->tester->getCredentialsForTestAutor()['id'],
            null
        );

        $this->assertEquals(409, $response->getStatusCode());
    }

    public function testSlotIsOccupied(): void
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $range = User::find($credentials['id']);

        $block = $this->createBlockWithSlotsForRange($range);
        $slot = $this->getSlotFromBlock($block);

        $this->createBooking(
            $credentials,
            $slot,
            $this->tester->getCredentialsForTestAutor()['id'],
            [201]
        );

        $response = $this->createBooking(
            $credentials,
            $slot,
            $this->tester->getCredentialsForTestAutor()['id'],
            null
        );

        $this->assertEquals(409, $response->getStatusCode());
    }

    public function testRootMayNotCreateBooking(): void
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $range = User::find($credentials['id']);

        $block = $this->createBlockWithSlotsForRange($range);
        $slot = $this->getSlotFromBlock($block);

        $response = $this->createBooking(
            $credentials,
            $slot,
            $this->tester->getCredentialsForRoot()['id'],
            null
        );

        $this->assertEquals(403, $response->getStatusCode());
    }

    private function createBooking(array $credentials, ConsultationSlot $slot, string $user_id, ?array $considered_succssfull): JsonApiResponse
    {
        return $this->sendMockRequest(
            '/consultation-bookings',
            BookingsCreate::class,
            $credentials,
            [],
            [
                'considered_successful' => $considered_succssfull,
                'method' => 'POST',
                'json_body' => [
                    'data' => [
                        'type' => Schema::TYPE,
                        'attributes' => [
                            'reason' => self::$BOOKING_DATA['reason'],
                        ],
                        'relationships' => [
                            Schema::REL_SLOT => [
                                'data' => [
                                    'type' => SlotSchema::TYPE,
                                    'id' => $slot->id,
                                ],
                            ],
                            Schema::REL_USER => [
                                'data' => [
                                    'type' => UserSchema::TYPE,
                                    'id' => $user_id,
                                ],
                            ],
                        ]
                    ]
                ]
            ]
        );
    }
}
