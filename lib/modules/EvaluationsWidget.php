<?php
/*
 * EvaluationsWidget.php - widget plugin for start page
 *
 * Copyright (C) 2014 - Nadine Werner <nadwerner@uos.de>
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class EvaluationsWidget extends CorePlugin implements PortalPlugin
{
    public function getPluginName()
    {
        return _('Fragebögen');
    }

    public function getMetadata()
    {
        return [
            'description' => _('Mit diesem Widget haben Sie Zugriff auf systemweite Umfragen.')
        ];
    }

    /**
     * Returns the portal widget template.
     *
     * Due to a seriously messed up architecture, the suppress_empty_output
     * variable is used to determine when an according message should be
     * presented to the user. If no evaluations and questionnaires are present,
     * the message from the questionnaires should be displayed. The according
     * message from evaluations should never be shown in the widget. Thus, the
     * evaluation controller will always suppress this message and the variable
     * is adjusted if the evaluation returned no content so that the
     * questionnaire controller will display it's message. If you think, we're
     * slowly running out of duct tape, you might be absolutely right...
     */
    public function getPortalTemplate()
    {
        if (!Config::get()->VOTE_ENABLE) {
            return null;
        }

        // include and show votes and tests
        $controller = app(AuthenticatedController::class, ['dispatcher' => app(\Trails_Dispatcher::class)]);
        $controller->suppress_empty_output = true;

        if (Config::get()->EVAL_ENABLE) {
            $response = $controller->relay('evaluation/display/studip')->body;
        }

        $controller->suppress_empty_output = (bool)$response;
        $response .= $controller->relay('questionnaire/widget/start')->body;

        $template = $GLOBALS['template_factory']->open('shared/string');
        $template->content = $response;

        if ($GLOBALS['perm']->have_perm('root')) {
            $navigation = new Navigation('', 'dispatch.php/questionnaire/overview');
            $navigation->setImage(Icon::create('add', 'clickable', ["title" => _('Umfragen bearbeiten')]));
            $template->icons = [$navigation];
        }
        return $template;
    }
}
