<?php

use Courseware\Instance;
use Courseware\StructuralElement;
use JsonApi\Routes\Courseware\StructuralElementsShow;
use JsonApi\Schemas\Courseware\StructuralElement as Schema;
use WoohooLabs\Yang\JsonApi\Response\JsonApiResponse;

class StructuralElementsShowTest extends \Codeception\Test\Unit
{
    /**
     * @var \JsonapiTester
     */
    protected $tester;

    protected function _before()
    {
        \DBManager::getInstance()->setConnection('studip', $this->getModule('\\Helper\\StudipDb')->dbh);
    }

    protected function _after()
    {
    }

    // tests
    public function testShouldShowStructuralElement()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $structuralElement = $this->createCourseware($credentials, 5);

        $response = $this->fetchStructuralElement($credentials, $structuralElement);
        $this->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->assertSame($structuralElement->id, $document->primaryResource()->id());
        $this->assertFalse($document->hasAnyIncludedResources());
    }

    public function testShouldIncludeChildren()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $structuralElement = $this->createCourseware($credentials, 5);

        $response = $this->fetchStructuralElement($credentials, $structuralElement, ['include' => 'children']);
        $this->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->assertSame($structuralElement->id, $document->primaryResource()->id());
        $this->assertTrue($document->hasAnyIncludedResources());

        $includedResources = $document->includedResources();
        $childIDs = $structuralElement->children->pluck('id');
        $this->assertCount(count($childIDs), $includedResources);
        foreach ($includedResources as $included) {
            $this->assertContains($included->id(), $childIDs);
        }
    }

    // **** helper functions ****
    private function createCourseware(iterable $credentials, int $numberOfChildren): StructuralElement
    {
        $instance = StructuralElement::createEmptyCourseware($credentials['id'], 'user');
        $root = $instance->getRoot();

        for ($i = 0; $i < $numberOfChildren; $i++) {
            $child = StructuralElement::build([
                'range_id' => $root['range_id'],
                'range_type' => $root['range_type'],
                'owner_id' => $root['owner_id'],
                'editor_id' => $root['editor_id'],
                'title' => _('neue Seite'),
            ]);

            $root->children[] = $child;
        }

        $root->store();

        return $root;
    }

    private function fetchStructuralElement(
        iterable $credentials,
        StructuralElement $resource,
        iterable $options = []
    ): JsonApiResponse {
        $app = $this->tester->createApp(
            $credentials,
            'get',
            '/courseware-structural-elements/{id}',
            StructuralElementsShow::class
        );

        $requestBuilder = $this->tester
            ->createRequestBuilder($credentials)
            ->setUri('/courseware-structural-elements/' . $resource->id)
            ->fetch();

        if (array_key_exists('include', $options)) {
            $requestBuilder->setJsonApiIncludes($options['include']);
        }

        return $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
    }
}
