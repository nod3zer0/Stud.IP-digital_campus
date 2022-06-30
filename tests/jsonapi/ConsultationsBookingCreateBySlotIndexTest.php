<?php
use JsonApi\Routes\Consultations\BookingsCreate;
use JsonApi\Schemas\ConsultationBooking as Schema;
use JsonApi\Schemas\User as UserSchema;
use WoohooLabs\Yang\JsonApi\Response\JsonApiResponse;

require_once __DIR__ . '/ConsultationHelper.php';

class ConsultationsBookingCreateBySlotIndexTest extends Codeception\Test\Unit
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
            '/consultation-slots/{id}/bookings',
            BookingsCreate::class,
            $credentials,
            ['id' => $slot->id],
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
                            Schema::REL_USER => [
                                'data' => [
                                    'type' => UserSchema::TYPE,
                                    'id' => $user_id,
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        );
    }
}
