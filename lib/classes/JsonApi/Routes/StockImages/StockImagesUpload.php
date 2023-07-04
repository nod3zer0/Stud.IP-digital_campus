<?php

namespace JsonApi\Routes\StockImages;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\NonJsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;
use Studip\StockImages\Scaler;
use Studip\StockImages\PaletteCreator;

class StockImagesUpload extends NonJsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $resource = \StockImage::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canUploadStockImage($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        $this->handleUpload($request, $resource);
        $this->processStockImage($resource);

        return $this->redirectToStockImage($response, $resource);
    }

    private function handleUpload(Request $request, \StockImage $resource): void
    {
        $uploadedFile = $this->getUploadedFile($request);
        if (UPLOAD_ERR_OK !== $uploadedFile->getError()) {
            $error = $this->getErrorString($uploadedFile->getError());
            throw new BadRequestException($error);
        }

        $error = self::validate($uploadedFile);
        if (!empty($error)) {
            throw new BadRequestException($error);
        }

        $resource->mime_type = $uploadedFile->getClientMediaType();
        $resource->size = $uploadedFile->getSize();
        $uploadedFile->moveTo($resource->getPath());

        $imageSize = getimagesize($resource->getPath());
        $resource->width = $imageSize[0];
        $resource->height = $imageSize[1];

        $resource->store();
    }

    private function getUploadedFile(Request $request): UploadedFile
    {
        $files = iterator_to_array($this->getUploadedFiles($request));

        if (0 === count($files)) {
            throw new BadRequestException('File upload required.');
        }

        if (count($files) > 1) {
            throw new BadRequestException('Multiple file upload not possible.');
        }

        $uploadedFile = reset($files);
        if (UPLOAD_ERR_OK !== $uploadedFile->getError()) {
            throw new BadRequestException('Upload error.');
        }

        return $uploadedFile;
    }

    /**
     * @return iterable<UploadedFile> a list of uploaded files
     */
    private function getUploadedFiles(Request $request): iterable
    {
        foreach ($request->getUploadedFiles() as $item) {
            if (!is_array($item)) {
                yield $item;
                continue;
            }
            foreach ($item as $file) {
                yield $file;
            }
        }
    }

    private function getErrorString(int $errNo): string
    {
        $errors = [
            UPLOAD_ERR_OK => 'There is no error, the file uploaded with success',
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
        ];

        return $errors[$errNo] ?? '';
    }

    /**
     * @return string|null null, if the file is valid, otherwise a string containing the error
     */
    private function validate(UploadedFile $file)
    {
        $mimeType = $file->getClientMediaType();
        if (!in_array($mimeType, ['image/gif', 'image/jpeg', 'image/png', 'image/webp'])) {
            return 'Unsupported media type.';
        }
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function redirectToStockImage(Response $response, \StockImage $stockImage): Response
    {
        $pathinfo = $this->getSchema($stockImage)
            ->getSelfLink($stockImage)
            ->getStringRepresentation($this->container->get('json-api-integration-urlPrefix'));
        $old = \URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        $url = \URLHelper::getURL($pathinfo, [], true);
        \URLHelper::setBaseURL($old);

        return $response->withHeader('Location', $url)->withStatus(201);
    }

    private function processStockImage(\StockImage $resource): void
    {
        $scaler = new Scaler();
        $scaler($resource);
        $paletteCreator = new PaletteCreator();
        $paletteCreator($resource);
    }
}
