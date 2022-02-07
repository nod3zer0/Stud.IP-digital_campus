<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\NonJsonApiController;
use Courseware\StructuralElement;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Copy an courseware structural element in an courseware structural element
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */

class StructuralElementsCopy extends NonJsonApiController
{
    public function __invoke(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody()['data'];

        $sourceElement = StructuralElement::find($args['id']);
        $newParent = StructuralElement::find($data['parent_id']);
        if (!Authority::canCreateContainer($user = $this->getUser($request), $newParent)) {
            throw new AuthorizationFailedException();
        }

        $newElement = $this->copyElement($user, $sourceElement, $newParent);
        if ($data['remove_purpose']) {
            $newElement->purpose = '';
        }

        return $this->redirectToStructuralElement($response, $newElement);
    }

    private function copyElement(\User $user, StructuralElement $sourceElement, StructuralElement $newParent)
    {
        $new_element = $sourceElement->copy($user, $newParent);

        return $new_element;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function redirectToStructuralElement(Response $response, StructuralElement $resource): Response
    {
        $pathinfo = $this->getSchema($resource)
            ->getSelfLink($resource)
            ->getStringRepresentation($this->container->get('json-api-integration-urlPrefix'));
        $old = \URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        $url = \URLHelper::getURL($pathinfo, [], true);
        \URLHelper::setBaseURL($old);

        return $response->withHeader('Location', $url)->withStatus(303);
    }
}
