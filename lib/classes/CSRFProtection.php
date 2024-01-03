<?php
# Lifter010: DONE

/**
 * CSRFProtection.php - protect from request forgery
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      mlunzena@uos.de
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * To protect Stud.IP from forged request from other sites a security token is
 * generated and stored in the session and all forms (or rather POST request)
 * have to contain that token which is then compared on the server side to
 * verify the authenticity of the request. GET request are not checked as these
 * are assumed to be idempotent anyway.
 *
 * If a forgery is detected, an InvalidSecurityTokenException is thrown and a
 * log entry is recorded in the error log.
 *
 * The (form or request) parameter is named "security token". If you are
 * authoring an HTML form, you have to include this as an
 * input[@type=hidden] element. This is easily done by calling:
 *
 * \code
 * echo CSRFProtection::tokenTag();
 * \endcode
 *
 * Checking the token is implicitly done when calling #page_open in file
 * lib/phplib/page4.inc
 */
class CSRFProtection
{
    /**
     * The name of the parameter.
     */
    const TOKEN = 'security_token';

    const AJAX_TOKEN = 'HTTP_X_CSRF_TOKEN';

    protected static $storage = null;

    /**
     * Set a storage to use.
     *
     * @param $storage
     */
    public static function setStorage(&$storage): void
    {
        self::$storage = &$storage;
    }

    /**
     * Returns a reference to the used storage.
     *
     * @return array|null
     */
    public static function &getStorage()
    {
        if (!isset(self::$storage)) {
            // w/o a session, throw an exception since we cannot use it
            if (session_id() === '') {
                throw new SessionRequiredException();
            }

            self::$storage = $_SESSION;
        }
        return self::$storage;
    }

    /**
     * This checks the request and throws an InvalidSecurityTokenException if
     * fails to verify its authenticity.
     *
     * @throws MethodNotAllowedException      The request has to be unsafe
     *                                        in terms of RFC 2616.
     * @throws InvalidSecurityTokenException  The request is invalid as the
     *                                        security token does not match.
     */
    public static function verifyUnsafeRequest()
    {
        if (self::isSafeRequestMethod()) {
            throw new MethodNotAllowedException();
        }

        if (!self::checkSecurityToken()) {
            throw new InvalidSecurityTokenException();
        }
    }

    /**
     * @return boolean true if the request method is either GET or HEAD
     */
    private static function isSafeRequestMethod()
    {
        return in_array(Request::method(), ['GET', 'HEAD']);
    }

    /**
     * This checks the request and throws an InvalidSecurityTokenException if
     * fails to verify its authenticity.
     *
     * @throws InvalidSecurityTokenException  request is invalid
     */
    public static function verifySecurityToken()
    {
        if (!self::verifyRequest()) {
            throw new InvalidSecurityTokenException();
        }
    }

    /**
     * This checks the request and returns either true or false. It is
     * implicitly called by CSRFProtection::verifySecurityToken() and
     * it should never be needed to call this.
     *
     * @returns boolean  returns true if the request is valid
     */
    public static function verifyRequest()
    {
        return Request::isGet() || self::checkSecurityToken();
    }

    /**
     * Verifies the equality of the request parameter "security_token" and
     * the token stored in the session.
     *
     * @return boolean  true if equal
     */
    private static function checkSecurityToken()
    {
        return self::token() === ($_POST[self::TOKEN] ?? $_SERVER[self::AJAX_TOKEN] ?? null);
    }

    /**
     * Returns the token stored in the session generating it first
     * if required.
     *
     * @return string  a base64 encoded string of 32 random bytes
     * @throws SessionRequiredException  there is no session to store the token in
     */
    public static function token()
    {
        $storage = &self::getStorage();

        // create a token, if there is none
        if (!isset($storage[self::TOKEN])) {
            $storage[self::TOKEN] = base64_encode(random_bytes(32));
        }

        return $storage[self::TOKEN];
    }

    /**
     * Returns a snippet of HTML containing an input[@type=hidden] element
     * like this:
     *
     * \code
     * <input type="hidden" name="security_token" value="012345678901234567890123456789==">
     * \endcode
     *
     * @param array $attributes Additional attributes to be added to the input
     * @return string  the HTML snippet containing the input element
     */
    public static function tokenTag(array $attributes = [])
    {
        $attributes = array_merge($attributes, [
            'name'  => self::TOKEN,
            'value' => self::token(),
        ]);

        return sprintf(
            '<input type="hidden" %s>',
            arrayToHtmlAttributes($attributes)
        );
    }
}
