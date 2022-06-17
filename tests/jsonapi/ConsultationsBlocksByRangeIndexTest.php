<?php
use JsonApi\Routes\Consultations\BlocksByRangeIndex;

require_once __DIR__ . '/ConsultationHelper.php';

// TODO: Activate consultations on institute for testing
class ConsultationsBlocksByRangeIndexTest extends Codeception\Test\Unit
{
    use ConsultationHelper;

    public static function rangeProvider(): array
    {
        return [
            'Course' => ['course', 'a07535cf2f8a72df33c12ddfa4b53dde'],
            'User'   => ['user', '205f3efb7997a0fc9755da2b535038da'],
        ];
    }

    /**
     * @dataProvider rangeProvider
     */
    public function testFetchBlocksByRangeIndex(string $range_type, string $range_id): void
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $range = RangeFactory::createRange($range_type, $range_id);

        $this->createBlockWithSlotsForRange($range);

        $response = $this->sendMockRequest(
            "/{type:courses|institutes|users}/{id}/consultations",
            BlocksByRangeIndex::class,
            $credentials,
            ['type' => "{$range_type}s", 'id' => $range_id]
        );
        $document = $this->getResourceCollectionDocument($response);

        $resources = $document->primaryResources();
        $this->tester->assertCount(1, $resources);
    }
}
