<?php

use JsonApi\Routes\Blubber\ThreadsCreate;
use JsonApi\Schemas\BlubberThread as Schema;
use JsonApi\Schemas\User as UsersSchema;

require_once 'BlubberTestHelper.php';

class BlubberThreadsCreateTest extends \Codeception\Test\Unit
{
    use BlubberTestHelper;

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        \DBManager::getInstance()->setConnection('studip', $this->getModule('\\Helper\\StudipDb')->dbh);

        // Create global template factory if neccessary
        $has_template_factory = isset($GLOBALS['template_factory']);
        if (!$has_template_factory) {
            $GLOBALS['template_factory'] = new Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH'] . '/templates');
        }
    }

    protected function _after()
    {
    }

    // tests
    public function testCreatePrivateThreadSucessfully()
    {
        // given
        $credentials = $this->tester->getCredentialsForTestAutor();

        $response = $this->createThread($credentials, 'private');
        $this->tester->assertTrue($response->isSuccessfulDocument([201]));

        $document = $response->document();
        $this->tester->assertTrue($document->isSingleResourceDocument());

        $resourceObject = $document->primaryResource();

        $this->tester->assertSame('private', $resourceObject->attribute('context-type'));
        $this->tester->assertSame('', $resourceObject->attribute('content'));

        // check predicates
        $this->tester->assertTrue($resourceObject->attribute('is-commentable'));
        $this->tester->assertTrue($resourceObject->attribute('is-readable'));
        $this->tester->assertTrue($resourceObject->attribute('is-writable'));
        $this->tester->assertTrue($resourceObject->attribute('is-visible-in-stream'));

        // check author relationship
        $authorRel = $resourceObject->relationship('author');
        $this->tester->assertTrue($authorRel->isToOneRelationship());
        $this->tester->assertCount(1, $links = $authorRel->resourceLinks());

        $this->tester->assertSame(UsersSchema::TYPE, $links[0]['type']);
        $this->tester->assertSame($credentials['id'], $links[0]['id']);

        // check mentions relationship
        $mentionsRel = $resourceObject->relationship('mentions');
        $this->tester->assertTrue($mentionsRel->isToManyRelationship());
        $this->tester->assertCount(1, $links = $mentionsRel->resourceLinks());

        $this->tester->assertSame(UsersSchema::TYPE, $links[0]['type']);
        $this->tester->assertSame($credentials['id'], $links[0]['id']);
    }

    public function testFailToCreateAnotherTypeOfThread()
    {
        // given
        $credentials = $this->tester->getCredentialsForTestAutor();

        $response = $this->createThread($credentials, 'course');
        $this->tester->assertSame(400, $response->getStatusCode());

        $response = $this->createThread($credentials, 'institute');
        $this->tester->assertSame(400, $response->getStatusCode());

        $response = $this->createThread($credentials, 'public');
        $this->tester->assertSame(400, $response->getStatusCode());
    }


    private function createThread($credentials, $contextType = 'private')
    {
        $body = [
            'data' => [
                'type' => Schema::TYPE,
                'attributes' => [
                    'context-type' => $contextType
                ]
            ]
        ];

        $app = $this->tester->createApp($credentials, 'post', '/blubber-threads', ThreadsCreate::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/blubber-threads')
            ->create()
            ->setJsonApiBody($body);

        return $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
    }
}
