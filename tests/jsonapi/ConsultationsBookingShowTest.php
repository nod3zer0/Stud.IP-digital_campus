<?php
use JsonApi\Routes\Consultations\BookingsShow;
use JsonApi\Schemas\ConsultationBooking as Schema;

require_once __DIR__ . '/ConsultationHelper.php';

class ConsultationsBookingShowTest extends Codeception\Test\Unit
{
    use ConsultationHelper;

    public function testFetchBlock(): void
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

        $response = $this->sendMockRequest(
            '/consultation-bookings/{id}',
            BookingsShow::class,
            $credentials,
            ['id' => $booking->id]
        );
        $document = $this->getSingleResourceDocument($response);

        $resourceObject = $document->primaryResource();
        $this->assertTrue(is_string($resourceObject->id()));
        $this->assertEquals($booking->id, $resourceObject->id());
        $this->assertSame(Schema::TYPE, $resourceObject->type());

        $this->assertEquals(self::$BOOKING_DATA['reason'], $resourceObject->attribute('reason'));

        $this->assertHasRelations($resourceObject, Schema::REL_SLOT, Schema::REL_USER);
    }
}
