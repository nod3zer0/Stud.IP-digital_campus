<?php
/*
 * studip_controller.php - studip controller base class
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once 'studip_controller_properties_trait.php';
require_once 'studip_response.php';

/**
 * @property StudipResponse $response
 */
abstract class StudipController extends Trails_Controller
{
    use StudipControllerPropertiesTrait;

    protected $with_session = false; //do we need to have a session for this controller
    protected $allow_nobody = true; //should 'nobody' allowed for this controller or redirected to login?
    protected $_autobind = false;

    /**
     * @return false|void
     */
    public function before_filter(&$action, &$args)
    {
        $this->current_action = $action;
        // allow only "word" characters in arguments
        $this->validate_args($args);

        parent::before_filter($action, $args);

        if ($this->with_session) {
            # open session
            page_open([
                'sess' => 'Seminar_Session',
                'auth' => $this->allow_nobody ? 'Seminar_Default_Auth' : 'Seminar_Auth',
                'perm' => 'Seminar_Perm',
                'user' => 'Seminar_User'
            ]);

            // show login-screen, if authentication is "nobody"
            $GLOBALS['auth']->login_if((Request::get('again') || !$this->allow_nobody) && $GLOBALS['user']->id == 'nobody');

            // Setup flash instance
            $this->flash = Trails_Flash::instance();

            // set up user session
            include 'lib/seminar_open.php';
        }

        // Set generic attribute that indicates whether the request was sent
        // via ajax or not
        $this->via_ajax = Request::isXhr();

        # Set base layout
        #
        # If your controller needs another layout, overwrite your controller's
        # before filter:
        #
        #   class YourController extends AuthenticatedController {
        #     function before_filter(&$action, &$args) {
        #       parent::before_filter($action, $args);
        #       $this->set_layout("your_layout");
        #     }
        #   }
        #
        # or unset layout by sending:
        #
        #   $this->set_layout(NULL)
        #
        $layout_file = Request::isXhr()
                     ? 'layouts/dialog.php'
                     : 'layouts/base.php';
        $layout = $GLOBALS['template_factory']->open($layout_file);
        $this->set_layout($layout);

        $this->set_content_type('text/html;charset=utf-8');
    }

    /**
     * Extended method to inject extended response object.
     */
    public function erase_response()
    {
        parent::erase_response();

        $this->response = new StudipResponse();
    }

    /**
     * Hooked perform method in order to inject body element id creation.
     *
     * In order to avoid clashes, these body element id will be joined
     * with a minus sign. Otherwise the controller "x" with action
     * "y_z" would be given the same id as the controller "x/y" with
     * the action "z", namely "x_y_z". With the minus sign this will
     * result in the ids "x-y_z" and "x_y-z".
     *
     * Plugins will always have a leading 'plugin-' and the decamelized
     * plugin name in front of the id.
     *
     * @param String $unconsumed_path Path segment containing action and
     *                                optionally arguments or format
     * @return Trails_Response from parent controller
     */
    public function perform($unconsumed_path)
    {
        // Set body element id if it has not already been set
        if (!PageLayout::hasBodyElementId()) {
            $body_id = $this->getBodyElementIdForControllerAndAction($unconsumed_path);
            PageLayout::setBodyElementId($body_id);
        }

        return parent::perform($unconsumed_path);
    }

    /**
     * Callback function being called after an action is executed.
     *
     * @param string Name of the action to perform.
     * @param array  An array of arguments to the action.
     *
     * @return void
     */
    public function after_filter($action, $args)
    {
        parent::after_filter($action, $args);

        if (Request::isXhr() && !isset($this->response->headers['X-Title']) && PageLayout::hasTitle()) {
            $this->response->add_header('X-Title', rawurlencode(PageLayout::getTitle()));
        }
        if (Request::isXhr() && !isset($this->response->headers['X-WikiLink']) && PageLayout::getHelpKeyword()) {
            $this->response->add_header('X-WikiLink', format_help_url(PageLayout::getHelpKeyword()));
        }

        if ($this->with_session) {
            page_close();
        }
    }

    /**
     * Validate arguments based on a list of given types. The types are:
     * 'int', 'float', 'option' and 'string'. If the list of types is NULL
     * or shorter than the argument list, 'option' is assumed for all
     * remaining arguments. 'option' differs from Request::option() in
     * that it also accepts the charaters '-' and ',' in addition to all
     * word characters.
     *
     * Since Stud.IP 4.0 it is also possible to directly inject
     * SimpleORMap objects. If types is NULL, the signature of the called
     * action is analyzed and any type hint that matches a sorm class
     * will be used to create an object using the argument as the id
     * that is passed to the object's constructor.
     *
     * If $_autobind is set to true, the created object is also assigned
     * to the controller so that it is available in a view.
     *
     * @param array $args  an array of arguments to the action
     * @param array $types list of argument types (optional)
     */
    public function validate_args(&$args, $types = null)
    {
        $class_infos = [];

        if ($types === null) {
            $types = [];
        }

        if ($this->has_action($this->current_action)) {
            $reflection = new ReflectionMethod($this, $this->current_action . '_action');
            $parameters = $reflection->getParameters();
            foreach ($parameters as $i => $parameter) {
                $class_type = $parameter->getType();

                if (
                    !$class_type
                    || !class_exists($class_type->getName())
                    || !is_a($class_type->getName(), SimpleORMap::class, true)
                ) {
                    continue;
                }

                $types[$i] = 'sorm';
                $class_infos[$i] = [
                    'model'    => $class_type->getName(),
                    'var'      => $parameter->getName(),
                    'optional' => $parameter->isOptional(),
                ];

                if ($parameter->isOptional() && !isset($args[$i])) {
                    $args[$i] = $parameter->getDefaultValue();
                }
            }
        }

        foreach ($args as $i => &$arg) {
            $type = $types[$i] ?? 'option';
            switch ($type) {
                case 'int':
                    $arg = (int) $arg;
                    break;

                case 'float':
                    $arg = (float) strtr($arg, ',', '.');
                    break;

                case 'option':
                    if (preg_match('/[^\\w,-]/', $arg)) {
                        throw new Trails_Exception(400);
                    }
                    break;

                case 'sorm':
                    $info = $class_infos[$i];

                    $id = null;
                    if ($arg != -1) {
                        $id = $arg;
                    }
                    if (mb_strpos($id, SimpleORMap::ID_SEPARATOR) !== false) {
                        $id = explode(SimpleORMap::ID_SEPARATOR, $id);
                    }

                    $reflection = new ReflectionClass($info['model']);

                    $sorm = $reflection->newInstance($id);
                    if (!$info['optional'] && $sorm->isNew()) {
                        throw new Trails_Exception(
                            404,
                            "Parameter {$info['var']} could not be resolved with value {$arg}"
                        );
                    }

                    $arg = $sorm;
                    if ($this->_autobind) {
                        $this->{$info['var']} = $arg;
                    }
                    break;

                case 'string':
                    break;

                default:
                    throw new Trails_Exception(500, 'Unknown type "' . $type . '"');
            }
        }

        reset($args);
    }

    /**
     * Returns a URL to a specified route to your Trails application.
     * without first parameter the current action is used
     * if route begins with a / then the current controller ist prepended
     * if second parameter is an array it is passed to URLHeper
     *
     * @param  string   a string containing a controller and optionally an action
     * @param  string[] optional arguments
     *
     * @return string  a URL to this route
     */
    public function url_for($to = ''/* , ... */)
    {
        $args = func_get_args();

        // Try to create route if none given
        if ($to === '') {
            $args[0] = isset($this->parent_controller)
                     ? $this->parent_controller->current_action
                     : $this->current_action;
            return $this->action_url(...$args);
        }

        // Create url for a specific action
        // TODO: This seems odd. You kinda specify an absolute path
        //       to receive a relative url. Meh...
        //
        // @deprecated Do not use this, please!
        if ($to[0] === '/') {
            $args[0] = substr($to, 1);
            return $this->action_url(...$args);
        }

        // Check for absolute URL
        if ($this->isURL($to)) {
            throw new InvalidArgumentException(__METHOD__ . ' cannot be used with absolute URLs');
        }

        // Extract fragment (if any)
        if (strpos($to, '#') !== false) {
            list($args[0], $fragment) = explode('#', $to);
        }

        // Extract parameters (if any)
        $params = [];
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        // Map any sorm objects to their ids
        $args = array_map(function ($arg) {
            if (is_object($arg) && $arg instanceof SimpleORMap) {
                return $arg->isNew() ? -1 : $arg->id;
            }
            return $arg;
        }, $args);

        $url = parent::url_for(...$args);

        if (isset($fragment)) {
            $url .= '#' . $fragment;
        }
        return URLHelper::getURL($url, $params);
    }

    /**
     * Returns an escaped URL to a specified route to your Trails application.
     * without first parameter the current action is used
     * if route begins with a / then the current controller ist prepended
     * if second parameter is an array it is passed to URLHeper
     *
     * @param  string   a string containing a controller and optionally an action
     * @param  strings  optional arguments
     *
     * @return string  a URL to this route
     */
    public function link_for($to = ''/* , ... */)
    {
        return htmlReady($this->url_for(...func_get_args()));
    }

    /**
     * Redirects the user another page. Accepts multiple parameters just like
     * url_for().
     *
     * @param string $to
     * @see StudipController::url_for()
     */
    public function redirect($to)
    {
        $to = $this->adjustToArguments(...func_get_args());

        parent::redirect($to);
    }

    /**
     * Relocate the user to another location. This is a specialized version
     * of redirect that differs in two points:
     *
     * - relocate() will force the browser to leave the current dialog while
     *   redirect would refresh the dialog's contents
     * - relocate() accepts all the parameters that url_for() accepts so it's
     *   no longer neccessary to chain url_for() and redirect()
     *
     * @param String $to Location to redirect to
     */
    public function relocate($to)
    {
        $to = $this->adjustToArguments(...func_get_args());

        if (Request::isDialog()) {
            $this->response->add_header('X-Location', encodeURI($to));
            $this->render_nothing();
        } else {
            parent::redirect($to);
        }
    }

    /**
     * Returns a URL to a specified route to your Trails application, unless
     * the parameter is already a valid URL (which is returned unchanged).
     *
     * If no absolute url or more than one argument is given, url_for() is
     * used.
     */
    private function adjustToArguments(...$args): string
    {
        if (count($args) > 1 && $this->isURL($args[0])) {
            throw new InvalidArgumentException('Method may not be used with a URL and multiple parameters');
        }

        if (count($args) === 1 && $this->isURL($args[0])) {
            return $args[0];
        }

        return $this->url_for(...$args);
    }

    /**
     * Returns whether the given parameter is a valid url.
     *
     * @param string $to
     * @return bool
     */
    private function isURL(string $to): bool
    {
        return preg_match('#^(/|\w+://)#', $to);
    }

    /**
     * Exception handler called when the performance of an action raises an
     * exception.
     *
     * @param  object     the thrown exception
     */
    public function rescue($exception)
    {
        throw $exception;
    }

    /**
     * render given data as json, data is converted to utf-8
     *
     * @param mixed $data
     */
    public function render_json($data)
    {
        $json = json_encode($data);

        $this->set_content_type('application/json;charset=utf-8');
        $this->response->add_header('Content-Length', strlen($json));
        $this->render_text($json);
    }

    /**
     * Render given data as csv, data is assumed to be utf-8.
     * The first row of data may contain column titles.
     *
     * @param array $data       data as two dimensional array
     * @param string $filename  download file name (optional)
     * @param string $delimiter field delimiter char (optional)
     * @param string $enclosure field enclosure char (optional)
     */
    public function render_csv($data, $filename = null, $delimiter = ';', $enclosure = '"')
    {
        $this->set_content_type('text/csv; charset=UTF-8');

        $output = fopen('php://temp', 'rw');
        fputs($output, "\xEF\xBB\xBF");

        foreach ($data as $row) {
            fputcsv($output, $row, $delimiter, $enclosure);
        }

        rewind($output);
        $csv_data = stream_get_contents($output);
        fclose($output);

        if (isset($filename)) {
            $this->response->add_header('Content-Disposition', 'attachment; ' . encode_header_parameter('filename', $filename));
        }

        $this->response->add_header('Content-Length', strlen($csv_data));

        $this->render_text($csv_data);
    }

    /**
     * Renders a pdf file given by a TCPDF/ExportPDF object.
     *
     * @param TCPDF   $pdf      TCPDF object to render
     * @param string  $filename Filename
     * @param bool    $inline   Should the pdf be displayed inline (default: no)
     */
    protected function render_pdf(TCPDF $pdf, $filename, $inline = false)
    {
        $temp_file = $GLOBALS['TMP_PATH'] . '/' . md5(uniqid('pdf-file', true));
        $pdf->Output($temp_file, 'F');

        $disposition = $inline ? 'inline' : 'attachment';

        $this->render_temporary_file($temp_file, $filename, 'application/pdf', $disposition);
    }

    /**
     * Renders a file
     * @param string  $file                Path of the file to render
     * @param string  $filename            Name of the file displayed to user
     *                                     (will equal $file when missing)
     * @param string  $content_type        Optional content type (will be determined if missing)
     * @param string  $content_disposition Either attachment (default) or inline
     * @param Closure $callback            Optional callback when download has finished
     * @param int     $chunk_size          Optional size of chunks to send (default: 256k)
     */
    public function render_file(
        $file,
        $filename = null,
        $content_type = null,
        $content_disposition = 'attachment',
        Closure $callback = null,
        $chunk_size = 262144
    ) {
        if (!file_exists($file)) {
            throw new Trails_Exception(404);
        }

        if (!is_readable($file)) {
            throw new Trails_Exception(500);
        }

        if ($content_type === null) {
            $content_type = get_mime_type($filename ?: $file);
        }

        if (!in_array($content_type, get_mime_types())) {
            $content_type = 'application/octet-stream';
        }

        if ($content_type === 'application/octet-stream') {
            $content_disposition = 'attachment';
        }

        $this->set_content_type($content_type);
        $this->response->add_header(
            'Content-Disposition',
            "{$content_disposition}; " . encode_header_parameter(
                'filename',
                FileManager::cleanFileName($filename ?: basename($file))
            )
        );
        $this->response->add_header('Content-Length', filesize($file));
        $this->response->add_header('Content-Transfer-Encoding',  'binary');
        $this->response->add_header('Pragma', 'public');
        $this->render_text(function () use ($file, $chunk_size, $callback) {
            $fp = fopen($file, 'rb');

            while (!feof($fp)) {
                yield fgets($fp, $chunk_size);
            }

            fclose($fp);

            if ($callback) {
                $callback($file);
            }
        });
    }

    /**
     * Renders a temporary file which will be deleted after transmission.
     * This is just a convenience method so you don't have to write the delete
     * callback.
     *
     * @param string  $file                Path of the file to render
     * @param string  $filename            Name of the file displayed to user
     *                                     (will equal $file when missing)
     * @param string  $content_type        Optional content type (will be determined if missing)
     * @param string  $content_disposition Either attachment (default) or inline
     * @param Closure $callback            Optional callback when download has finished
     * @param int     $chunk_size          Optional size of chunks to send (default: 256k)
     */
    public function render_temporary_file(
        $file,
        $filename = null,
        $content_type = null,
        $content_disposition = 'attachment',
        Closure $callback = null,
        $chunk_size = 262144

    ) {
        $delete_callback = function ($file) use ($callback) {
            unlink($file);

            if ($callback) {
                $callback($file);
            }
        };

        $this->render_file(
            $file,
            $filename,
            $content_type,
            $content_disposition,
            $delete_callback,
            $chunk_size
        );
    }

    public function render_form(\Studip\Forms\Form $form)
    {
        $this->render_text($form->render());
    }

    /**
     * relays current request to another controller and returns the response
     * the other controller is given all assigned properties, additional parameters are passed
     * through
     *
     * @param string $to_uri a trails route
     * @return Trails_Response
     */
    public function relay($to_uri/* , ... */)
    {
        $args = func_get_args();
        $uri = array_shift($args);
        [$controller_path, $unconsumed] = '' === $uri ? $this->dispatcher->default_route() : $this->dispatcher->parse($uri);

        $controller = $this->dispatcher->load_controller($controller_path);
        $assigns = $this->get_assigned_variables();
        unset($assigns['controller']);
        foreach ($assigns as $k => $v) {
            $controller->$k = $v;
        }
        $controller->layout = null;
        $controller->parent_controller = $this;
        array_unshift($args, $unconsumed);
        return $controller->perform_relayed(...$args);
    }

    /**
     * Relays current request and performs redirect if neccessary.
     *
     * @param string $to_uri a trails route
     * @return Trails_Response
     *
     * @see StudipController::relay()
     */
    public function relayWithRedirect(...$args): Trails_Response
    {
        $response = $this->relay(...$args);

        // If the relayed action should perform a redirect, do so
        if (isset($response->headers['Location'])) {
            header("Location: {$response->headers['Location']}");
            page_close();
            die;
        }

        return $response;
    }

    /**
     * perform a given action/parameter string from an relayed request
     * before_filter and after_filter methods are not called
     *
     * @see perform
     * @param string $unconsumed
     * @return Trails_Response
     */
    public function perform_relayed($unconsumed/* , ... */)
    {
        $args = func_get_args();
        $unconsumed = array_shift($args);

        [$action, $extracted_args, $format] = $this->extract_action_and_args($unconsumed);
        $this->format = isset($format) ? $format : 'html';
        $this->current_action = $action;
        $args = array_merge($extracted_args, $args);
        $callable = $this->map_action($action);

        if (is_callable($callable)) {
            $callable(...$args);
        } else {
            $this->does_not_understand($action, $args);
        }

        if (!$this->performed) {
            $this->render_action($action);
        }
        return $this->response;
    }

    /**
     * Renders a given template and returns the resulting string.
     *
     * @param string $template Name of the template file
     * @param mixed  $layout   Optional layout
     * @return string
     */
    public function render_template_as_string($template, $layout = null)
    {
        $template = $this->get_template_factory()->open($template);
        $template->set_layout($layout);
        $template->set_attributes($this->get_assigned_variables());
        return $template->render();
    }

    /**
     * Magic methods that intercepts all unknown method calls.
     * If a method is called that matches an action on this controller,
     * an url to that action is generated.
     *
     * Basically, this:
     *
     *    <code>$controller->url_for('foo/bar/baz/' . $param)</code>
     *
     * is equal to calling this on the Foo_BarController:
     *
     *    <code>$controller->baz($param)</code>
     *
     * @param String $method    Called method name
     * @param array  $argumetns Provided arguments
     * @return url to the requested action
     * @throws Trails_UnknownAction if no action matches the method
     */
    public function __call($method, $arguments)
    {
        $function = 'action_link';
        if (mb_strpos($method, 'Link') === mb_strlen($method) - 4) {
            $method = mb_substr($method, 0, -4);
        } elseif (mb_strpos($method, 'URL') === mb_strlen($method) - 3) {
            $function = 'action_url';
            $method = mb_substr($method, 0, -3);
        }

        if (!$this->has_action($method)) {
            throw new Trails_UnknownAction("Unknown action '{$method}'");
        }

        array_unshift($arguments, $method);
        return call_user_func_array([$this, $function], $arguments);
    }

    /**
     * Returns whether this controller has the specificed action.
     *
     * @param string $action Name of the action
     * @return true if action is defined, false otherwise
     */
    public function has_action($action)
    {
        return method_exists($this, $action . '_action')
            || ($this->parent_controller
                && $this->parent_controller->has_action($action));
    }

    /**
     * Generates the url for an action on this controller without the
     * neccessity to provide the full "path" to the action (since it
     * is implicitely known).
     *
     * Basically, this:
     *
     *    <code>$controller->url_for('foo/bar/baz/' . $param)</code>
     *
     * is equal to calling this on the Foo_BarController:
     *
     *    <code>$controller->action_url('baz/' . $param)</code>
     *
     * @param string $action Name of the action
     * @return string url to the requested action
     */
    public function action_url($action)
    {
        $arguments = func_get_args();
        $arguments[0] = $this->controller_path() . '/' . $arguments[0];

        return $this->url_for(...$arguments);
    }

    /**
     * Generates the link for an action on this controller without the
     * neccessity to provide the full "path" to the action (since it
     * is implicitely known).
     *
     * Basically, this:
     *
     *    <code>$controller->link_for('foo/bar/baz/' . $param)</code>
     *
     * is equal to calling this on the Foo_BarController:
     *
     *    <code>$controller->action_link('baz/' . $param)</code>
     *
     * @param string $action Name of the action
     * @return string to the requested action
     */
    public function action_link($action)
    {
        return htmlReady($this->action_url(...func_get_args()));
    }

    /**
     * Returns the url path to this controller.
     *
     * @return string url path to this controller
     */
    protected function controller_path()
    {
        $class = get_class($this->parent_controller ?? $this);
        $controller = mb_substr($class, 0, -mb_strlen('Controller'));
        $controller = strtosnakecase($controller);
        return preg_replace('/_{2,}/', '/', $controller);
    }


    /**
     * Validate the datetime according to specific format.
     *
     * @param string $datetime the datetime which should be validate
     * @param string $format the format that the datetime should have by default H:i for time
     *
     * @return bool result of validation
     */
    public function validate_datetime($datetime, $format = 'H:i')
    {
        $dt = DateTime::createFromFormat($format, $datetime);
        return $dt && $dt->format($format) == date('H:i',strtotime($datetime));
    }

    /**
     * Export xlsx and csv files via PhpSpreadsheet
     *
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function render_spreadsheet(
        array $header,
        array $data,
        string $format,
        string $filename,
        ?string $filepath = null
    ): void  {
        $render_to_browser = false;
        if ($filepath == null) {
            $render_to_browser = true;
            $filepath = tempnam($GLOBALS['TMP_PATH'], 'spreadsheet');
        }
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->fromArray($header);
        $activeWorksheet->fromArray($data, null, 'A2');

        if ($format === 'xlsx') {
            $writer = new Xlsx($spreadsheet);
        } elseif ($format === 'csv') {
            $writer = new Csv($spreadsheet);
        } else {
            throw new Exception("Format {$format} is not supported");
        }

        $writer->save($filepath);

        if ($render_to_browser) {
            $this->response->add_header('Cache-Control', 'cache, must-revalidate');
            $this->render_temporary_file(
                $filepath,
                $filename,
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );
        }
    }

    /**
     * Creates the body element id for this controller a given action.
     *
     * @param string $unconsumed_path Unconsumed path to extract action from
     * @return string
     */
    protected function getBodyElementIdForControllerAndAction($unconsumed_path)
    {
        // Extract action from unconsumed path segment
        [$action] = $this->extract_action_and_args($unconsumed_path);

        // Extract controller name from class name
        $controller = preg_replace('/Controller$/', '', get_class($this));
        $controller = Trails_Inflector::underscore($controller);

        // Build main parts of the body element id
        $body_id_parts = explode('/', $controller);
        $body_id_parts[] = $action;

        // Create and set body element id
        $body_id = implode('-', $body_id_parts);

        return $body_id;
    }
}
