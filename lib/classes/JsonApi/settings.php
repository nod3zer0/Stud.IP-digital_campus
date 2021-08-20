<?php

namespace JsonApi;

use DI\ContainerBuilder;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        'studip-current-user' => function () {
            if ($user = $GLOBALS['user']) {
                return $user->getAuthenticatedUser();
            }

            return null;
        },
        'studip-authenticator' => function () {
            return function ($username, $password) {
                $check = \StudipAuthAbstract::CheckAuthentication($username, $password);

                if ($check['uid'] && 'nobody' != $check['uid']) {
                    return \User::find($check['uid']);
                }

                return null;
            };
        },

        'json-api-integration-schemas' => function () {
            $schemaMap = new SchemaMap();
            $coreSchemas = $schemaMap();

            $allSchemas = $coreSchemas;
            $pluginSchemas = \PluginEngine::sendMessage(Contracts\JsonApiPlugin::class, 'registerSchemas');
            if (is_array($pluginSchemas) && count($pluginSchemas)) {
                foreach ($pluginSchemas as $arrayOfSchemas) {
                    $allSchemas = array_merge($allSchemas, $arrayOfSchemas);
                }
            }

            return $allSchemas;
        },

        'json-api-integration-urlPrefix' => function () {
            return rtrim(\URLHelper::getUrl('jsonapi.php/v1'), '/');
        },

        'json-api-error-encoder' => function (FactoryInterface $factory) {
            return $factory->createEncoder($factory->createSchemaContainer([]));
        },
    ]);
};
