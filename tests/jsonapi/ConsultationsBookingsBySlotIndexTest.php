<?php
use JsonApi\Routes\Consultations\BookingsBySlotIndex;

require_once __DIR__ . '/ConsultationHelper.php';

class ConsultationsBookingsBySlotIndexTest extends Codeception\Test\Unit
{
    use ConsultationHelper;

    public function testFetchSlots(): void
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $range = $this->getUserForCredentials($credentials);

        $block = $this->createBlockWithSlotsForRange($range);
        $slot = $this->getSlotFromBlock($block);
        $this->createBookingForSlot(
            $credentials,
            $slot,
            $this->getUserForCredentials($this->tester->getCredentialsForTestAutor())
        );

        $response = $this->sendMockRequest(
            '/consultation-slots/{id}/bookings',
            BookingsBySlotIndex::class,
            $credentials,
            ['id' => $slot->id]
        );
        $document = $this->getResourceCollectionDocument($response);

        $resources = $document->primaryResources();
        $this->tester->assertCount(1, $resources);
    }
}
