<?php
use WoohooLabs\Yang\JsonApi\Response\JsonApiResponse;
use WoohooLabs\Yang\JsonApi\Schema\Document;
use WoohooLabs\Yang\JsonApi\Schema\Resource\ResourceObject;

// Required for consultation mailer
require_once 'vendor/flexi/flexi.php';

trait ConsultationHelper
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        \DBManager::getInstance()->setConnection('studip', $this->getModule('\\Helper\\StudipDb')->dbh);
    }

    protected static $BLOCK_DATA = [
        'room'              => 'Testraum',
        'calendar_events'   => false,
        'show_participants' => false,
        'require_reason'    => 'no',
        'confirmation_text' => null,
        'note'              => 'Testnotiz für Block',
        'size'              => 1,
    ];

    protected static $SLOT_DATA = [
        'note' => 'Testnotiz für Slot',
    ];

    protected static $BOOKING_DATA = [
        'reason' => 'Test reason',
    ];

    protected function getUserForCredentials(array $credentials): User
    {
        return User::find($credentials['id']);
    }

    protected function createBlockWithSlotsForRange(Range $range): ConsultationBlock
    {
        $blocks = ConsultationBlock::generateBlocks(
            $range,
            strtotime('today 8:00'),
            strtotime('today 10:00'),
            date('w'),
            1
        );
        $blocks = iterator_to_array($blocks);

        $block = reset($blocks);
        $block->setData(self::$BLOCK_DATA);

        $block->slots->exchangeArray($block->createSlots(15));
        foreach ($block->slots as $slot) {
            $slot->setData(self::$SLOT_DATA['note']);
        }

        $block->store();

        return ConsultationBlock::find($block->id);
    }

    protected function getSlotFromBlock(ConsultationBlock $block): ConsultationSlot
    {
        return $block->slots->first();
    }

    protected function withStudipEnv(array $credentials, callable $fn)
    {
        // Create global template factory if neccessary
        $has_template_factory = isset($GLOBALS['template_factory']);
        if (!$has_template_factory) {
            $GLOBALS['template_factory'] = new Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH'] . '/templates');
        }

        $result = $this->tester->withPHPLib($credentials, $fn);

        if (!$has_template_factory) {
            unset($GLOBALS['template_factory']);
        }

        return $result;
    }

    protected function createBookingForSlot(array $credentials, ConsultationSlot $slot, User $user): ConsultationBooking
    {
        return $this->withStudipEnv(
            $credentials,
            function () use ($slot, $user): ConsultationBooking {
                $booking = new ConsultationBooking();
                $booking->slot_id = $slot->id;
                $booking->user_id = $user->id;

                $booking->setData(self::$BOOKING_DATA);

                $booking->store();

                return $booking;
            }
        );
    }

    protected function sendMockRequest(string $route, string $handler, array $credentials, array $variables = [], array $options = []): JsonApiResponse
    {
        $options = array_merge([
            'method'                => 'GET',
            'considered_successful' => [200],
            'json_body'             => null,
        ], $options);

        $app = $this->tester->createApp(
            $credentials,
            strtolower($options['method']),
            $route,
            $handler
        );

        $evaluated_route = preg_replace_callback(
            '/\{(.+?)(:[^}]+)?}/',
            function ($match) use ($variables) {
                $key = $match[1];
                if (!isset($variables[$key])) {
                    throw new Exception("No variable '{$key}' defined");
                }
                return $variables[$key];
            },
            $route
        );

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder->setUri($evaluated_route)->setMethod(strtoupper($options['method']));

        if (isset($options['json_body'])) {
            $requestBuilder->setJsonApiBody($options['json_body']);

        }

        /** @var JsonApiResponse $response */
        $response = $this->withStudipEnv($credentials, function () use ($app, $requestBuilder) {
            return $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
        });

        if ($options['considered_successful']) {
            $this->assertTrue(
                $response->isSuccessful($options['considered_successful']),
                'Actual status code is ' . $response->getStatusCode()
            );
        }

        return $response;
    }

    protected function getSingleResourceDocument(JsonApiResponse $response): Document
    {
        $this->assertTrue($response->hasDocument());

        $document = $response->document();
        $this->assertTrue($document->isSingleResourceDocument());

        return $document;
    }

    protected function getResourceCollectionDocument(JsonApiResponse $response): Document
    {
        $this->assertTrue($response->hasDocument());

        $document = $response->document();
        $this->assertTrue($document->isResourceCollectionDocument());

        return $document;
    }

    protected function assertHasRelations(ResourceObject $resource, ...$relations)
    {
        foreach ($relations as $relation) {
            $this->assertTrue($resource->hasRelationship($relation));
        }
    }
}
