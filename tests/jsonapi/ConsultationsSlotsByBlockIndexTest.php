<?php
use JsonApi\Routes\Consultations\SlotsByBlockIndex;

require_once __DIR__ . '/ConsultationHelper.php';

class ConsultationsSlotsByBlockIndexTest extends Codeception\Test\Unit
{
    use ConsultationHelper;

    public function testFetchSlots(): void
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $range = User::find($credentials['id']);

        $block = $this->createBlockWithSlotsForRange($range);

        $response = $this->sendMockRequest(
            '/consultation-blocks/{id}/slots',
            SlotsByBlockIndex::class,
            $credentials,
            ['id' => $block->id]
        );
        $document = $this->getResourceCollectionDocument($response);

        $resources = $document->primaryResources();
        $this->tester->assertCount(8, $resources);
    }
}
