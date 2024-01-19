<?php
/*
 * Copyright (c) 2011  Rasmus Fuhse
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * Controller called by the main periodical ajax-request. It collects data,
 * converts the textstrings to utf8 and returns it as a json-object to the
 * internal javascript-function "STUDIP.JSUpdater.process(json)".
 */
class JsupdaterController extends AuthenticatedController
{
    // Allow nobody to prevent login screen
    // Refers to http://develop.studip.de/trac/ticket/4771
    protected $allow_nobody = true;

    /**
     * Checks whether we have a valid logged in user,
     * send "Forbidden" otherwise.
     *
     * @param String $action The action to perform
     * @param Array  $args   Potential arguments
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Check for a valid logged in user (only when an ajax request occurs)
        if (Request::isXhr() && (!is_object($GLOBALS['user']) || $GLOBALS['user']->id === 'nobody')) {
            $this->response->set_status(403);
            $action = 'nop';
        }
    }

    /**
     * Does and renders absolute nothing.
     */
    public function nop_action()
    {
        $this->render_nothing();
    }

    /**
     * Main action that returns a json-object like
     * {
     *  'js_function.sub_function': data,
     *  'anotherjs_function.sub_function': moredata
     * }
     * This action is called by STUDIP.JSUpdater.poll and the result processed
     * the internal STUDIP.JSUpdater.process method
     */
    public function get_action()
    {
        UpdateInformation::setInformation("server_timestamp", time());
        $data = UpdateInformation::getInformation();
        $data = array_merge($data, $this->coreInformation());

        $this->set_content_type('application/json;charset=utf-8');
        $this->render_text(json_encode($data));
    }

    /**
     * Marks a personal notification as read by the user so it won't be displayed
     * in the list in the header.
     * @param string $id : hash-id of the notification
     */
    public function mark_notification_read_action($id)
    {
        if ($id === 'all') {
            PersonalNotifications::markAllAsRead();
        } else {
            PersonalNotifications::markAsRead($id);
        }

        $url = false;
        if ($id === 'all') {
            $url = Request::get('return_to');
        } elseif (!Request::isXhr() || Request::isDialog()) {
            $notification = new PersonalNotifications($id);
            $url = $notification->url;
        }

        if ($url) {
            $this->redirect(URLHelper::getURL(TransformInternalLinks($url)));
        } else {
            $this->render_nothing();
        }
    }

    /**
     * Sets the background-color of the notification-number to blue, so it does
     * not annoy the user anymore. But he/she is still able to see the notificaion-list.
     * Just sets a unix-timestamp in the user-config NOTIFICATIONS_SEEN_LAST_DATE.
     */
    public function notifications_seen_action()
    {
        UserConfig::get($GLOBALS['user']->id)->store('NOTIFICATIONS_SEEN_LAST_DATE', time());
        $this->render_text(time());
    }

    /**
     * SystemPlugins may call UpdateInformation::setInformation to set information
     * to be sent via ajax to the main request. Core-functionality-data should be
     * collected and set here.
     * @return array: array(array('index' => $data), ...)
     */
    protected function coreInformation()
    {
        $pageInfo = Request::getArray("page_info");
        $data = [
            'coursewareclipboard' => $this->getCoursewareClipboardUpdates($pageInfo),
            'blubber' => $this->getBlubberUpdates($pageInfo),
            'messages' => $this->getMessagesUpdates($pageInfo),
            'personalnotifications' => $this->getPersonalNotificationUpdates($pageInfo),
            'questionnaire' => $this->getQuestionnaireUpdates($pageInfo),
            'wiki_page_content' => $this->getWikiPageContents($pageInfo),
            'wiki_editor_status' => $this->getWikiEditorStatus($pageInfo),
        ];

        return array_filter($data);
    }

    private function getBlubberUpdates($pageInfo)
    {
        $data = [];
        if (isset($pageInfo['blubber']['threads']) && is_array($pageInfo['blubber']['threads'])) {
            $blubber_data = [];
            foreach ($pageInfo['blubber']['threads'] as $thread_id) {
                $thread = new BlubberThread($thread_id);
                if ($thread->isReadable()) {
                    $comments = BlubberComment::findBySQL(
                        "thread_id = :thread_id AND chdate >= :time ORDER BY mkdate ASC",
                        ['thread_id' => $thread_id, 'time' => UpdateInformation::getTimestamp()]
                    );
                    foreach ($comments as $comment) {
                        $blubber_data[$thread_id][] = $comment->getJSONdata();
                    }
                }
            }
            if (count($blubber_data)) {
                $data['addNewComments'] = $blubber_data;
            }
            $statement = DBManager::get()->prepare("
                SELECT blubber_events_queue.item_id
                FROM blubber_events_queue
                WHERE blubber_events_queue.event_type = 'delete'
            ");
            $statement->execute([$pageInfo['blubber']['threads']]);
            $comment_ids = $statement->fetchAll(PDO::FETCH_COLUMN, 0);
            if (count($comment_ids)) {
                $data['removeDeletedComments'] = $comment_ids;
            }
            $statement = DBManager::get()->prepare("
                DELETE FROM blubber_events_queue
                WHERE mkdate <= UNIX_TIMESTAMP() - 60 * 15
            ");
            $statement->execute();
        }
        if (mb_stripos(Request::get("page"), "dispatch.php/blubber") !== false) {
            //collect updated threads for the widget
            $threads = BlubberThread::findMyGlobalThreads(30, UpdateInformation::getTimestamp());
            $thread_widget_data = [];
            foreach ($threads as $thread) {
                $thread_widget_data[] = [
                    'thread_id' => $thread->getId(),
                    'avatar' => $thread->getAvatar(),
                    'name' => $thread->getName(),
                    'timestamp' => (int) $thread->getLatestActivity()
                ];
            }
            if (count($thread_widget_data)) {
                $data['updateThreadWidget'] = $thread_widget_data;
            }
        }

        return $data;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function getMessagesUpdates($pageInfo)
    {
        $data = [];
        if (mb_stripos(Request::get("page"), "dispatch.php/messages") !== false) {
            $messages = Message::findNew(
                $GLOBALS["user"]->id,
                $pageInfo['messages']['received'],
                $pageInfo['messages']['since'],
                $pageInfo['messages']['tag']
            );
            $templateFactory = $this->get_template_factory();
            foreach ($messages as $message) {
                $attributes = [
                    'message' => $message,
                    'received' => $pageInfo['messages']['received'],
                    'controller' => $this,
                ];
                $html = $templateFactory->open("messages/_message_row.php")
                                         ->render($attributes);
                $data['messages'][$message->getId()] = $html;
            }
        }

        return $data;
    }

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     */
    private function getPersonalNotificationUpdates($pageInfo)
    {
        $data = [];
        if (PersonalNotifications::isActivated()) {
            $notifications = PersonalNotifications::getMyNotifications();
            if ($notifications && count($notifications)) {
                $ret = [];
                foreach ($notifications as $notification) {
                    $info = $notification->toArray();
                    $info['html'] = $notification->getLiElement();
                    $ret[] = $info;
                }
                $data['notifications'] = $ret;
            } else {
                $data['notifications'] = [];
            }
        }

        return $data;
    }

    private function getQuestionnaireUpdates($pageInfo)
    {
        if (
            !isset($pageInfo['questionnaire']['questionnaire_ids'])
            || !is_array($pageInfo['questionnaire']['questionnaire_ids'])
        ) {
            return [];
        }

        $data = [];
        Questionnaire::findEachMany(
            function (Questionnaire $questionnaire) use ($pageInfo, &$data) {
                if ($questionnaire->latestAnswerTimestamp() > $pageInfo['questionnaire']['last_update']) {
                    $template = $this->get_template_factory()->open("questionnaire/evaluate");
                    $template->filtered = $pageInfo['questionnaire']['filtered'];
                    $template->set_layout(null);
                    $template->set_attribute("questionnaire", $questionnaire);
                    $data[$questionnaire->id] = [
                        'html' => $template->render()
                    ];
                }
            },
            $pageInfo['questionnaire']['questionnaire_ids']
        );

        return $data;
    }

    private function getWikiPageContents($pageInfo): array
    {
        $data = [];
        if (!empty($pageInfo['wiki_page_content'])) {
            foreach ($pageInfo['wiki_page_content'] as $page_id) {
                $page = WikiPage::find($page_id);
                if ($page && $page->isReadable() && ($page->chdate >= Request::int('server_timestamp'))) {
                    $data['contents'][$page_id] = wikiReady($page->content, true, $page->range_id, $page->id);
                }
            }
        }
        return $data;
    }

    private function getWikiEditorStatus($pageInfo): array
    {
        $data = [];
        if (!empty($pageInfo['wiki_editor_status']['page_ids'])) {
            $user = User::findCurrent();
            foreach ((array) $pageInfo['wiki_editor_status']['page_ids'] as $page_id) {
                WikiOnlineEditingUser::deleteBySQL(
                    "`page_id` = :page_id AND `chdate` < UNIX_TIMESTAMP() - :threshold",
                    [
                        'page_id' => $page_id,
                        'threshold' => WikiOnlineEditingUser::$threshold
                    ]
                );
                $page = WikiPage::find($page_id);
                if ($page && $page->isEditable()) {
                    if (
                        $pageInfo['wiki_editor_status']['focussed'] == $page_id
                        && !empty($pageInfo['wiki_editor_status']['page_content'])
                    ) {
                        $page->content = \Studip\Markup::markAsHtml(
                            $pageInfo['wiki_editor_status']['page_content']
                        );
                        $page->store();
                    }
                    $onlineData = [
                        'user_id' => $user->id,
                        'page_id' => $page_id
                    ];
                    $online = WikiOnlineEditingUser::findOneBySQL(
                        "`user_id` = :user_id AND `page_id` = :page_id",
                        $onlineData
                    );
                    if (!$online) {
                        $online = WikiOnlineEditingUser::build($onlineData);
                    }
                    $editingUsers = WikiOnlineEditingUser::countBySQL(
                        "`page_id` = ? AND `editing` = 1 AND `user_id` != ?",
                        [$page->id, $user->id]
                    );
                    if ($editingUsers > 0) {
                        $online->editing = 0;
                    } elseif ($online->editing && $online->editing_request) {
                        // this is the mode that this user requested the editing mode and was granted to get it:
                        $online->editing_request = 0;
                    } elseif ($online->editing_request) {
                        $other_requests = WikiOnlineEditingUser::countBySql("`page_id` = ? AND `editing_request` = 1 AND `user_id` != ?", [
                            $page->id,
                            $user->id,
                        ]);
                        if ($other_requests === 0) {
                            $online->editing_request = 0;
                            $online->editing = 1;
                        }
                    } else {
                        if ($pageInfo['wiki_editor_status']['focussed'] == $page_id) {
                            $online->editing = 1;
                        } else {
                            $other_users = WikiOnlineEditingUser::countBySql("`page_id` = ? AND `user_id` != ?", [
                                $page->id,
                                $user->id,
                            ]);
                            if ($other_users === 0) {
                                // if I'm the only user I don't need to lose the edit mode
                                $online->editing = 1;
                            } else {
                                $online->editing = 0;
                            }
                        }
                    }
                    $online->chdate = time();
                    $online->store();
                    $data['contents'][$page_id] = wikiReady($page->content, true, $page->range_id, $page_id);
                    $data['wysiwyg_contents'][$page_id] = $page->content;
                    $data['pages'][$page_id]['editing'] = $online->editing;
                } else {
                    $data['pages'][$page_id]['editing'] = 0;
                }
                $data['pages'][$page_id]['chdate'] = $page->chdate;
                $data['users'][$page_id] = $page->getOnlineUsers();
            }
        }
        return $data;
    }

    private function getCoursewareClipboardUpdates($pageInfo)
    {
        $counter = $pageInfo['coursewareclipboard']['counter'] ?? 0;
        return \Courseware\Clipboard::countByUser_id($GLOBALS['user']->id) != $counter;
    }
}
