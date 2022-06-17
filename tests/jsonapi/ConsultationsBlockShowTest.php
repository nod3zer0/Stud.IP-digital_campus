<?php
use JsonApi\Routes\Consultations\BlockShow;
use JsonApi\Schemas\ConsultationBlock as Schema;

require_once __DIR__ . '/ConsultationHelper.php';

class ConsultationsBlockShowTest extends Codeception\Test\Unit
{
    use ConsultationHelper;

    public function testFetchBlock(): void
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $range = User::find($credentials['id']);

        $block = $this->createBlockWithSlotsForRange($range);

        $response = $this->sendMockRequest(
            '/consultation-blocks/{id}',
            BlockShow::class,
            $credentials,
            ['id' => $block->id]
        );
        $document = $this->getSingleResourceDocument($response);

        $resourceObject = $document->primaryResource();
        $this->assertTrue(is_string($resourceObject->id()));
        $this->assertSame($block->id, $resourceObject->id());
        $this->assertSame(Schema::TYPE, $resourceObject->type());

        $this->assertEquals($block->start, strtotime($resourceObject->attribute('start')));
        $this->assertEquals($block->end, strtotime($resourceObject->attribute('end')));

        $this->assertSame(self::$BLOCK_DATA['room'], $resourceObject->attribute('room'));
        $this->assertSame(self::$BLOCK_DATA['show_participants'], $resourceObject->attribute('show-participants'));
        $this->assertSame(self::$BLOCK_DATA['require_reason'], $resourceObject->attribute('require-reason'));
        $this->assertSame(self::$BLOCK_DATA['confirmation_text'], $resourceObject->attribute('confirmation-text'));
        $this->assertSame(self::$BLOCK_DATA['note'], $resourceObject->attribute('note'));
        $this->assertSame(self::$BLOCK_DATA['size'], $resourceObject->attribute('size'));

        $this->assertHasRelations($resourceObject, Schema::REL_RANGE, Schema::REL_SLOTS);
    }
}
