><?php
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use JsonApi\Routes\Files\NegotiateFileRefsCreate as FileRefsCreate;
use JsonApi\Schemas\ContentTermsOfUse;
use JsonApi\Schemas\FileRef;
use Slim\Psr7\Factory\ServerRequestFactory;

require_once 'FilesTestHelper.php';

class FileRefsCreateTest extends \Codeception\Test\Unit
{
    use FilesTestHelper;

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        \DBManager::getInstance()->setConnection('studip', $this->getModule('\\Helper\\StudipDb')->dbh);
    }

    protected function _after()
    {
    }

    public function testShouldCreateFileRefInFolder()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $folder = $this->prepareTopFolder($credentials, $courseId);
        $license = $this->getSampleLicense();

        $name = 'filename.jpg';
        $description = 'a description';

        $response = $this->sendCreateFileRefInFolder($credentials, $folder, $name, $description, $license);

        $this->assertFileRefCreated($response, $name, $description, $license);
    }

    public function testShouldFailOnMissingFolder()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $missingFolder = \Folder::buildExisting(['id' => 'foo']);
        $license = $this->getSampleLicense();

        $name = 'filename.jpg';
        $description = 'a description';

        $response = $this->sendCreateFileRefInFolder($credentials, $missingFolder, $name, $description, $license);
        $this->tester->assertSame(404, $response->getStatusCode());
    }

    public function testShouldFailOnEmptyName()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $folder = $this->prepareTopFolder($credentials, $courseId);
        $license = $this->getSampleLicense();

        $name = '';
        $description = 'a description';

        $response = $this->sendCreateFileRefInFolder($credentials, $folder, $name, $description, $license);
        $this->tester->assertSame(422, $response->getStatusCode());
    }

    public function testShouldFailOnMissingLicense()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $folder = $this->prepareTopFolder($credentials, $courseId);

        $name = 'a-real-filename.gif';
        $description = 'a description';

        $response = $this->sendCreateFileRefInFolder($credentials, $folder, $name, $description, null);
        $this->tester->assertSame(422, $response->getStatusCode());
    }

    public function testShouldCreateLinkIfSameUser()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $folder = $this->prepareTopFolder($credentials, $courseId);
        $license = $this->getSampleLicense();

        $file = $this->createFileInFolder($credentials, $folder, 'name.jpg', 'some description');

        $numFiles = \File::countBySQL('1');
        $numFileRefs = \FileRef::countBySQL('1');

        $response = $this->sendCopyFileInFolder(
            $credentials,
            $folder,
            $file,
            $name = 'another-name.jpg',
            $description = 'another description',
            $license
        );

        // same number of Files and one more FileRef
        $this->assertSame($numFiles + 0, \File::countBySQL('1'));
        $this->assertSame($numFileRefs + 1, \FileRef::countBySQL('1'));

        $this->assertFileRefCreated($response, $name, $description, $license);
    }

    public function testShouldCreateCopyUnlessSameUser()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $credentialsAutor = $this->tester->getCredentialsForTestAutor();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $folder = $this->prepareTopFolder($credentials, $courseId);
        $license = $this->getSampleLicense();

        $file = $this->createFileInFolder($credentials, $folder, 'name.jpg', 'some description');

        $numFiles = \File::countBySQL('1');
        $numFileRefs = \FileRef::countBySQL('1');

        $response = $this->sendCopyFileInFolder(
            $credentialsAutor,
            $folder,
            $file,
            $name = 'another-name.jpg',
            $description = 'another description',
            $license
        );

        // one more File and one more FileRef
        $this->assertSame($numFiles + 1, \File::countBySQL('1'));
        $this->assertSame($numFileRefs + 1, \FileRef::countBySQL('1'));

        $this->assertFileRefCreated($response, $name, $description, $license);
    }

    public function testShouldCreateFileRefByUpload()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $folder = $this->prepareTopFolder($credentials, $courseId);
        $license = $this->getSampleLicense();

        $name = 'tiny.gif';
        $filename = __DIR__ . '/' . $name;
        $description = 'a description';
        $content = base64_decode('R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
        if (!file_exists($filename)) {
            file_put_contents($filename, $content);
        }
        $this->tester->assertTrue(file_exists($filename));
        $file = new \Slim\Psr7\UploadedFile($this->fileToStreamInterface($filename), $name);

        $app = $this->tester->createApp($credentials, 'POST', '/folders/{id}/file-refs', FileRefsCreate::class);

        $factory = new ServerRequestFactory();
        $serverParams = [
            'PHP_AUTH_USER' => $credentials['username'],
            'PHP_AUTH_PW' => $credentials['password'],
        ];
        $request = $factory->createServerRequest('POST', '/folders/' . $folder->id . '/file-refs', $serverParams);
        $request = $request->withUploadedFiles([$file])->withHeader('Content-Type', 'multipart/form-data');

        $response = $this->tester->sendMockRequest($app, $request);
        $this->tester->assertSame(201, $response->getStatusCode());
        $this->tester->assertArrayHasKey('Location', $response->getHeaders());
    }

    // **** helper functions ****
    private function sendCreateFileRefInFolder($user, $folder, $name, $description, $license)
    {
        $app = $this->tester->createApp($user, 'POST', '/folders/{id}/file-refs', FileRefsCreate::class);

        $requestBuilder = $this->tester->createRequestBuilder($user);
        $requestBuilder
            ->setJsonApiBody($this->prepareValidFileRefBody($name, $description, $license))
            ->setUri('/folders/' . $folder->id . '/file-refs')
            ->create();

        return $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
    }

    private function sendCopyFileInFolder($credentials, $folder, $file, $name, $description, $license)
    {
        $app = $this->tester->createApp($credentials, 'POST', '/folders/{id}/file-refs', FileRefsCreate::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setJsonApiBody($this->prepareValidFileRefBody($name, $description, $license, $file))
            ->setUri('/folders/' . $folder->id . '/file-refs')
            ->create();

        return $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
    }

    private function assertFileRefCreated($response, $name, $description, $license)
    {
        $this->tester->assertTrue($response->isSuccessfulDocument([201]));

        $document = $response->document();
        $this->tester->assertTrue($document->isSingleResourceDocument());

        $resource = $document->primaryResource();
        $this->tester->assertNotEmpty($resource->id());
        $this->tester->assertSame(FileRef::TYPE, $resource->type());

        $this->tester->assertSame($name, $resource->attribute('name'));
        $this->tester->assertSame($description, $resource->attribute('description'));

        $resourceLink = $resource->relationship('terms-of-use')->firstResourceLink();
        $this->tester->assertSame($license->id, $resourceLink['id']);
    }

    private function fileToStreamInterface(string $filename)
    {
        $factory = new \Slim\Psr7\Factory\StreamFactory();

        return $factory->createStreamFromFile($filename);
    }
}
