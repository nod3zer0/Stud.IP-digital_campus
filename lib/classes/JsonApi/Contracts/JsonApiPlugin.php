<?php

namespace JsonApi\Contracts;

/**
 * Stud.IP-Plugins, die dieses Interface implementieren, können
 * JSON-API-Routen zu Verfügung stellen.
 */
interface JsonApiPlugin
{
    /**
     * In dieser Methode können Plugins eigene autorisierte Routen
     * eintragen lassen.
     *
     * Dazu müssen am übergebenen \Slim\Routing\RouteCollectorProxy-Objekt die Methoden
     * \Slim\Routing\RouteCollectorProxy::get, \Slim\Routing\RouteCollectorProxy::post,
     * \Slim\Routing\RouteCollectorProxy::put, \Slim\Routing\RouteCollectorProxy::delete
     * oder \Slim\Routing\RouteCollectorProxy::patch aufgerufen werden.
     *
     * Beispiel:
     *     class Blubber ... implements JsonApiPlugin
     *     {
     *         public function registerAuthenticatedRoutes(\Slim\Routing\RouteCollectorProxy $group)
     *         {
     *             $group->get('/blubbers', BlubbersIndex::class);
     *         }
     *         [...]
     *     }
     *
     * @param \Slim\Routing\RouteCollectorProxy $group die Slim-Applikation, in der das Plugin
     *                       Routen eintragen möchte
     *
     * @return void
     */
    public function registerAuthenticatedRoutes(\Slim\Routing\RouteCollectorProxy $group);

    /**
     * In dieser Methode können Plugins eigene Routen ohne Autorisierung
     * eintragen lassen.
     *
     * Dazu müssen am übergebenen \Slim\Routing\RouteCollectorProxy-Objekt die Methoden
     * \Slim\Routing\RouteCollectorProxy::get, \Slim\Routing\RouteCollectorProxy::post,
     * \Slim\Routing\RouteCollectorProxy::put, \Slim\Routing\RouteCollectorProxy::delete
     * oder \Slim\Routing\RouteCollectorProxy::patch aufgerufen werden.
     *
     * Beispiel:
     *     class Blubber ... implements JsonApiPlugin
     *     {
     *         public function registerUnauthenticatedRoutes(\Slim\Routing\RouteCollectorProxy $group)
     *         {
     *             $group->get('/blubbers', BlubbersIndex::class);
     *         }
     *         [...]
     *     }
     *
     * @param \Slim\Routing\RouteCollectorProxy $group die Slim-Applikation, in der das Plugin
     *                       Routen eintragen möchte
     *
     * @return void
     */
    public function registerUnauthenticatedRoutes(\Slim\Routing\RouteCollectorProxy $group);

    /**
     * In dieser Methode können Plugins Schemata den verwendeten
     * Model-Klassen (also in der Regel SORM-Klassen) zuordnen.
     *
     * Wenn man in einer JSON-API-Route (als zum Beispiel einem
     * Unterklasse von \JsonApi\JsonApiController), Models
     * zurückgeben möchte, müssen für diese Models Schemata hinterlegt
     * worden sein.
     *
     * Beispiel:
     *     class Blubber ... implements JsonApiPlugin
     *     {
     *         public function registerSchema()
     *         {
     *             return [
     *                \BlubberPosting::class => \BlubberPostingSchema::class
     *             ];
     *         }
     *         [...]
     *     }
     *
     * @return array ein Array von Zuordnungen von Model-Klassennamen
     *               zu den entsprechenden Schema-Klassennamen
     */
    public function registerSchemas(): array;
}
