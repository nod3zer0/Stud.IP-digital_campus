<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\NonJsonApiController;
use Courseware\StructuralElement;
use JsonApi\Errors\AuthorizationFailedException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Link an courseware structural element to another courseware structural element
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.2
 */

class StructuralElementsLink extends NonJsonApiController
{
    public function __invoke(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody()['data'];

        $targetElement = StructuralElement::find($args['id']);
        $parent = StructuralElement::find($data['parent_id']);
        $user = $this->getUser($request);

        if (!Authority::canCreateStructuralElement($user, $parent) || !Authority::canUpdateStructuralElement($user, $targetElement)) {
            throw new AuthorizationFailedException();
        }

        $newElement = $this->linkElement($user, $targetElement, $parent);

        return $this->redirectToStructuralElement($response, $newElement);
    }

    private function linkElement(\User $user, StructuralElement $targetElement, StructuralElement $parent)
    {
        $newElement = $targetElement->link($user, $parent);

        return $newElement;
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