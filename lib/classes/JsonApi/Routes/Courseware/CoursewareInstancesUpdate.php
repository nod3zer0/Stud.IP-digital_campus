<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Instance;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Instance as InstanceSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update one courseware instance.
 */
class CoursewareInstancesUpdate extends JsonApiController
{
    use CoursewareInstancesHelper, ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $chunks = explode('_', $args['id']);
        $rangeType = $chunks[0];
        $rangeId = $chunks[1];
        $unitId = $chunks[2] ?? null;

        $resource = $this->findInstanceWithRange($rangeType, $rangeId . '_' . $unitId);
        $json = $this->validate($request, $resource);
        if (!Authority::canUpdateCoursewareInstance($user = $this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $resource = $this->updateInstance($user, $resource, $json);

        return $this->getContentResponse($resource);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        if (InstanceSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }

        if (!self::arrayHas($json, 'data.id')) {
            return 'Document must have an `id`.';
        }

        if (self::arrayHas($json, 'data.attributes.favorite-block-types')) {
            $favoriteBlockTypes = self::arrayGet($json, 'data.attributes.favorite-block-types');
            if (!is_array($favoriteBlockTypes)) {
                return 'Attribute `favorite-block-types` must be an array.';
            }
            $blockTypes = array_map(function ($blockType) {
                return $blockType::getType();
            }, $data->getBlockTypes());
            foreach ($favoriteBlockTypes as $favoriteBlockType) {
                if (!in_array($favoriteBlockType, $blockTypes)) {
                    return 'Attribute `favorite-block-types` contains an invalid block type.';
                }
            }
        } 
        
        if (self::arrayHas($json, 'data.attributes.sequential-progression')) {
            $sequentialProgression = self::arrayGet($json, 'data.attributes.sequential-progression');
            if (!in_array($sequentialProgression, [0, 1])) {
                return 'Attribute `sequential-progression` must be 0 or 1.';
            }
        }

        if (self::arrayHas($json, 'data.attributes.root-layout')) {
            $rootLayout = self::arrayGet($json, 'data.attributes.root-layout');
            if (!is_string($rootLayout)) {
                return 'Attribute `root-layout` must be a string.';
            }
            if (!$data->isValidRootLayout($rootLayout)) {
                return 'Attribute `root-layout` contains an invalid value.';
            }
        }

        if (self::arrayHas($json, 'data.attributes.editing-permission-level')) {
            $editingPermissionLevel = self::arrayGet($json, 'data.attributes.editing-permission-level');
            if (!is_string($editingPermissionLevel)) {
                return 'Attribute `editing-permission-level` must be a string.';
            }
            if (!$data->isValidEditingPermissionLevel($editingPermissionLevel)) {
                return 'Attribute `editing-permission-level` contains an invalid value.';
            }
        }

        if (self::arrayHas($json, 'data.attributes.show-feedback-popup')) {
            $showFeedbackPopup = self::arrayGet($json, 'data.attributes.show-feedback-popup');
            if (!in_array($showFeedbackPopup, [0,1])) {
                return 'Attribute `show-feedback-popup` must be 0 or 1.';
            }
        }

        if (self::arrayHas($json, 'data.attributes.show-feedback-in-contentbar')) {
            $showFeedbackInContentbar = self::arrayGet($json, 'data.attributes.show-feedback-in-contentbar');
            if (!in_array($showFeedbackInContentbar, [0,1])) {
                return 'Attribute `show-feedback-in-contentbar` must be 0 or 1.';
            }
        }

        if (self::arrayHas($json, 'data.attributes.certificate-settings')) {
            $certificateSettings = self::arrayGet($json, 'data.attributes.certificate-settings');

            if (!$data->isValidCertificateSettings($certificateSettings)) {
                return 'Attribute `certificate-settings` contains an invalid value.';
            }
        }

        if (self::arrayHas($json, 'data.attributes.reminder-settings')) {
            $reminderSettings = self::arrayGet($json, 'data.attributes.reminder-settings');

            if (!$data->isValidReminderSettings($reminderSettings)) {
                return 'Attribute `reminder-settings` contains an invalid value.';
            }
        }

        if (self::arrayHas($json, 'data.attributes.reset-progress-settings')) {
            $resetProgressSettings = self::arrayGet($json, 'data.attributes.reset-progress-settings');

            if (!$data->isValidResetProgressSettings($resetProgressSettings)) {
                return 'Attribute `reset-progress-settings` contains an invalid value.';
            }
        }
    }

    private function updateInstance(\User $user, Instance $instance, array $json): Instance
    {
        $get = function ($key, $default = '') use ($json) {
            return self::arrayGet($json, $key, $default);
        };

        $favorites = $get('data.attributes.favorite-block-types');
        $instance->setFavoriteBlockTypes($user, $favorites);

        $rootLayout = $get('data.attributes.root-layout');
        $instance->setRootLayout($rootLayout);

        $sequentialProgression = $get('data.attributes.sequential-progression');
        $instance->setSequentialProgression($sequentialProgression);

        $editingPermissionLevel = $get('data.attributes.editing-permission-level');
        $instance->setEditingPermissionLevel($editingPermissionLevel);

        $showFeedbackPopup = $get('data.attributes.show-feedback-popup');
        $instance->setShowFeedbackPopup($showFeedbackPopup);

        $showFeedbackInContentbar = $get('data.attributes.show-feedback-in-contentbar');
        $instance->setShowFeedbackInContentbar($showFeedbackInContentbar);

        $certificateSettings = $get('data.attributes.certificate-settings');
        $instance->setCertificateSettings($certificateSettings);

        $reminderSettings = $get('data.attributes.reminder-settings');
        $instance->setReminderSettings($reminderSettings);

        $resetProgressSettings = $get('data.attributes.reset-progress-settings');
        $instance->setResetProgressSettings($resetProgressSettings);

        // Store changes in unit configuration.
        $instance->getUnit()->store();

        return $instance;
    }
}
