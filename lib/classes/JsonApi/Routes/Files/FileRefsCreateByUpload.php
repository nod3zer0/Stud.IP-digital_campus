<?php

namespace JsonApi\Routes\Files;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\NonJsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FileRefsCreateByUpload extends NonJsonApiController
{
    use RoutesHelperTrait;

    public function invoke(Request $request, Response $response, array $args): Response
    {
        if (!$folder = \FileManager::getTypedFolder($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canCreateFileRefsInFolder($this->getUser($request), $folder)) {
            throw new AuthorizationFailedException();
        }

        $term_id = $request->getParsedBody()['term-id'];

        $fileRef = $this->handleUpload($request, $folder);

        if ($term_id) {
            $fileRef->content_terms_of_use_id = $term_id;
            $fileRef->store();
        }

        return $this->redirectToFileRef($response, $fileRef);
    }
}
