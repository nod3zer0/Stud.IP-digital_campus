<?php
/**
 * lti.php - LTI 1.1 single sign on controller
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class LtiController extends AuthenticatedController
{
    /**
     * Callback function being called before an action is executed.
     */
    public function before_filter(&$action, &$args)
    {
        // enforce LTI SSO login
        Request::set('sso', 'lti');

        parent::before_filter($action, $args);
    }

    /**
     * Redirect to enrollment action for the given course, if needed.
     */
    public function index_action($course_id = null)
    {
        $course_id = Request::option('custom_cid', $course_id);
        $course_id = Request::option('custom_course', $course_id);
        $message_type = Request::option('lti_message_type');

        if ($message_type === 'ContentItemSelectionRequest') {
            $_SESSION['ContentItemSelection'] = [
                'oauth_consumer_key' => Request::get('oauth_consumer_key'),
                'content_item_return_url' => Request::get('content_item_return_url'),
                'document_targets' => Request::get('accept_presentation_document_targets'),
                'data' => Request::get('data')
            ];
            $this->redirect('lti/content_item');
        } else if ($course_id) {
            $this->redirect('course/enrolment/apply/' . $course_id);
        } else {
            $this->redirect('start');
        }
    }

    /**
     * Select course for ContentItemSelectionRequest message.
     */
    public function content_item_action()
    {
        PageLayout::setTitle(_('Veranstaltung verknüpfen'));
        Navigation::activateItem('/browse/my_courses/content_item');

        $this->document_targets = $_SESSION['ContentItemSelection']['document_targets'];
        $this->target_labels = [
            'embed'   => _('in Seite einbetten'),
            'frame'   => _('gleiches Fenster oder Tab'),
            'iframe'  => _('IFrame in der Seite'),
            'window'  => _('neues Fenster oder Tab'),
            'popup'   => _('Popup-Fenster'),
            'overlay' => _('Dialog'),
            'none'    => _('nicht anzeigen')
        ];

        $sql = "JOIN seminar_user USING(Seminar_id)
                WHERE user_id = ? AND seminar_user.status IN ('dozent', 'tutor')
                ORDER BY start_time DESC, Name";
        $this->courses = Course::findBySQL($sql, [$GLOBALS['user']->id]);
    }

    /**
     * Return the selected content item to the LTI consumer.
     */
    public function link_content_item_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        $course_id = Request::option('course_id');
        $target = Request::option('target');
        $course = Course::find($course_id);

        $consumer_key = $_SESSION['ContentItemSelection']['oauth_consumer_key'];
        $return_url = $_SESSION['ContentItemSelection']['content_item_return_url'];
        $data = $_SESSION['ContentItemSelection']['data'];
        unset($_SESSION['ContentItemSelection']);

        $consumer_config = $GLOBALS['STUDIP_AUTH_CONFIG_LTI']['consumer_keys'][$consumer_key];
        $consumer_secret = $consumer_config['consumer_secret'];
        $signature_method = $consumer_config['signature_method'] ?? 'sha1';

        $content_items = [
            '@context' => 'http://purl.imsglobal.org/ctx/lti/v1/ContentItem',
            '@graph' => []
        ];

        if (Request::submitted('link')) {
            $content_items['@graph'][] = [
                '@type' => 'LtiLinkItem',
                'mediaType' => 'application/vnd.ims.lti.v1.ltilink',
                'title' => $course->name,
                'text' => $course->beschreibung,
                'placementAdvice' => ['presentationDocumentTarget' => $target],
                'custom' => ['course' => $course_id]
            ];
        }

        // set up ContentItemSelection
        $lti_link = new LtiLink($return_url, $consumer_key, $consumer_secret, $signature_method);
        $lti_link->addLaunchParameters([
            'lti_message_type' => 'ContentItemSelection',
            'content_items' => json_encode($content_items),
            'data' => $data
        ]);

        $this->launch_url = $lti_link->getLaunchURL();
        $this->launch_data = $lti_link->getBasicLaunchData();
        $this->signature = $lti_link->getLaunchSignature($this->launch_data);
        $this->render_template('course/lti/iframe');
    }
}
