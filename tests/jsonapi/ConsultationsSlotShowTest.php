<?php
use JsonApi\Routes\Consultations\SlotShow;
use JsonApi\Schemas\ConsultationSlot as Schema;

require_once __DIR__ . '/ConsultationHelper.php';

class ConsultationsSlotShowTest extends Codeception\Test\Unit
{
    use ConsultationHelper;

    public function testFetchBlock(): void
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $range = User::find($credentials['id']);

        $block = $this->createBlockWithSlotsForRange($range);
        $slot = $this->getSlotFromBlock($block);

        $response = $this->sendMockRequest(
            '/consultation-slots/{id}',
            SlotShow::class,
            $credentials,
            ['id' => $slot->id]
        );
        $document = $this->getSingleResourceDocument($response);

        $resourceObject = $document->primaryResource();
        $this->assertTrue(is_string($resourceObject->id()));
        $this->assertEquals($slot->id, $resourceObject->id());
        $this->assertSame(Schema::TYPE, $resourceObject->type());

        $this->assertEquals($slot->start_time, strtotime($resourceObject->attribute('start_time')));
        $this->assertEquals($slot->end_time, strtotime($resourceObject->attribute('end_time')));

        $this->assertHasRelations($resourceObject, Schema::REL_BLOCK, Schema::REL_BOOKINGS);

//
//        $this->assertSame(self::$BLOCK_DATA['room'], $resourceObject->attribute('room'));
//        $this->assertSame(self::$BLOCK_DATA['show_participants'], $resourceObject->attribute('show-participants'));
//        $this->assertSame(self::$BLOCK_DATA['require_reason'], $resourceObject->attribute('require-reason'));
//        $this->assertSame(self::$BLOCK_DATA['confirmation_text'], $resourceObject->attribute('confirmation-text'));
//        $this->assertSame(self::$BLOCK_DATA['note'], $resourceObject->attribute('note'));
//        $this->assertSame(self::$BLOCK_DATA['size'], $resourceObject->attribute('size'));
    }
}
