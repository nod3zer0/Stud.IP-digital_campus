<?php
namespace JsonApi\Routes\Consultations;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\ConflictException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\ConsultationSlot;
use JsonApi\Schemas\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BookingsCreate extends JsonApiController
{
    use ValidationTrait;

    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request, $args);

        $slot = $this->getBookingSlot($json, $args);
        $user = $this->getUser($request);
        $booking_user = $this->getBookingUser($json);

        if (!Authority::canBookSlotForUser($user, $slot, $booking_user)) {
            throw new AuthorizationFailedException();
        }

        if ($slot->isOccupied()) {
            throw new ConflictException('The slot is already occupied');
        }

        if (!$slot->userMayCreateBookingForSlot($booking_user)) {
            throw new ConflictException('The slot is locked for bookings');
        }

        $booking = \ConsultationBooking::create([
            'slot_id' => $slot->id,
            'user_id' => $booking_user->id,
            'reason'  => self::arrayGet($json, 'data.attributes.reason', ''),
        ]);

        return $this->getCreatedResponse($booking);
    }

    protected function validateResourceDocument($json, $data)
    {
        $user_validation_error = $this->validateRequestContainsValidUser($json, $data);
        $slot_validation_error = $this->validateRequestContainsValidSlot($json, $data);

        return $user_validation_error ?? $slot_validation_error;
    }

    protected function validateRequestContainsValidUser($json, $data)
    {
        if (!self::arrayHas($json, 'data.relationships.user')) {
            return 'No user relationship defined for booking';
        }

        $booking_user = self::arrayGet($json, 'data.relationships.user');
        if (!isset($booking_user['data']['type'], $booking_user['data']['id']) || $booking_user['data']['type'] !== User::TYPE) {
            return 'Defined booking user has invalid format.';
        }
        if (!\User::exists($booking_user['data']['id'])) {
            return 'Defined booking user does not exist.';
        }

        return null;
    }

    protected function validateRequestContainsValidSlot($json, $data)
    {
        if (isset($data['id']) && \ConsultationSlot::exists($data['id'])) {
            return null;
        }

        if (!self::arrayHas($json, 'data.relationships.slot')) {
            return 'No slot relationship defined for booking';
        }

        $booking_slot = self::arrayGet($json, 'data.relationships.slot');
        if (!isset($booking_slot['data']['type'], $booking_slot['data']['id']) || $booking_slot['data']['type'] !== ConsultationSlot::TYPE) {
            return 'Defined slot for booking has invalid format.';
        }
        if (!\ConsultationSlot::exists($booking_slot['data']['id'])) {
            return 'Defined slot for booking does not exist.';
        }

        return null;
    }

    protected function getBookingUser($json): \User
    {
        $user_id = self::arrayGet($json, 'data.relationships.user.data.id');
        return \User::find($user_id);
    }

    protected function getBookingSlot($json, $data): \ConsultationSlot
    {
        $slot_id = self::arrayGet($json, 'data.relationships.slot.data.id', $data['id'] ?? null);
        return \ConsultationSlot::find($slot_id);
    }
}
