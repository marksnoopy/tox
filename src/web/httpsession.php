<?php
/**
 * Session provides session-level data management and the related configurations.
 *
 * This file is part of Tox.
 *
 * Tox is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Tox is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tox.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   Tox\Web
 * @author    Qiang Fu <fuqiang007enter@gmail.com>
 * @copyright © 2012 szen.in
 * @license   http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Web;

use Tox\Core;


class HttpSession extends Core\Assembly implements IHttpSession
{
    protected $sessionData;

    public function __construct()
    {
        if (session_id() != '') {
            throw new SessionAlreadyStartException(array('session_id' => session_id()));
        }
    }


    public function init($config)
    {
        if ($this->useMemcachedStoreSession()) {
            @session_set_save_handler(
                            array($this, 'openSession'), array($this, 'closeSession'), array($this, 'readSession'), array($this, 'writeSession'), array($this, 'destroySession'), array($this, 'gcSession'));
        }

        session_start();

        $this->sessionData = $_SESSION;

        register_shutdown_function(array($this, 'close'));
    }

    public function useMemcachedStoreSession()
    {
        return false;
    }

    /**
     * Ends the current session and store session data.
     */
    public function close()
    {
        if (session_id() !== '') {
            $_SESSION = $this->sessionData;
            @session_write_close();
        }
    }

    /**
     * Frees all session variables and destroys all data registered to a session.
     */
    public function destroy()
    {
        if (session_id() !== '') {
            @session_unset();
            @session_destroy();
        }
    }

    /**
     * @return string the current session ID
     */
    public function getSessionID()
    {
        return session_id();
    }

    /**
     * Adds a session variable.
     *
     * @param mixed $key   session variable name
     *
     * @param mixed $value session variable value
     */
    public function setSession($key, $value)
    {
        $this->sessionData[$key] = $value;
    }

    public function getSession($key)
    {
        return isset($this->sessionData[$key]) ? $this->sessionData[$key] : null;
    }

    /**
     * Removes a session variable.
     *
     * @param  mixed $key the name of the session variable to be removed
     *
     * @return mixed the removed value, null if no such session variable.
     */
    public function removeSession($key)
    {
        if (isset($this->sessionData[$key])) {
            $value = $this->sessionData[$key];
            unset($this->sessionData[$key]);
            return $value;
        }
        else
            return null;
    }

    /**
     * Removes all session variables
     */
    public function clearSession()
    {
        foreach (array_keys($this->sessionData) as $key)
            unset($this->sessionData[$key]);
    }

    /**
     * @return array the list of all session variables in array
     */
    public function Export()
    {
        return $this->sessionData;
    }

    /**
     * @return integer the number of seconds after which data will be seen as 'garbage' and cleaned up, defaults to 1440 seconds.
     */
    public function getTimeout()
    {
        return (int) ini_get('session.gc_maxlifetime');
    }

    /**
     * @param integer $value the number of seconds after which data will be seen as 'garbage' and cleaned up
     */
    public function setTimeout($value)
    {
        ini_set('session.gc_maxlifetime', $value);
    }

    /**
     * @return string the current session save path, defaults to '/tmp'.
     */
    public function getSavePath()
    {
        return session_save_path();
    }

    /**
     * @param  string     $value the current session save path
     * @throws CException if the path is not a valid directory
     */
    public function setSavePath($value)
    {
        if (is_dir($value)) {
            session_save_path($value);
        }
        else{
            throw new SessionSavePathNotVaildException(array('savePath' => $value));
        }
    }

    /**
     * @return array the session cookie parameters.
     */
    public function getCookieParams()
    {
        return session_get_cookie_params();
    }

    /**
     * Sets the session cookie parameters.
     *
     * Call this method before the session starts.
     *
     * @param array $value cookie parameters, valid keys include: lifetime, path, domain, secure.
     */
    public function setCookieParams($value)
    {
        $data = session_get_cookie_params();
        extract($data);
        extract($value);
        if (isset($httponly)){
            session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
        }
        else{
            session_set_cookie_params($lifetime, $path, $domain, $secure);
        }
    }

    /**
     * Session open handler.
     *
     * Do not call this method directly.
     *
     * @param string $savePath session save path
     *
     * @param string $sessionName session name
     *
     * @return boolean whether session is opened successfully
     */
    public function openSession($savePath, $sessionName)
    {
        return true;
    }

    /**
     * Session close handler.
     *
     * This method should be overridden if {@link useCustomStorage} is set true.
     * Do not call this method directly.
     *
     * @return boolean whether session is closed successfully
     */
    public function closeSession()
    {
        return true;
    }

    /**
     * Session read handler.
     *
     * This method should be overridden if {@link useCustomStorage} is set true.
     *
     * Do not call this method directly.
     *
     * @param  string $id session ID
     * @return string     the session data
     */
    public function readSession($id)
    {
        return '';
    }

    /**
     * Session write handler.
     *
     * This method should be overridden if {@link useCustomStorage} is set true.
     *
     * Do not call this method directly.
     *
     * @param string $id   session ID
     *
     * @param string $data session data
     *
     * @return boolean whether session write is successful
     */
    public function writeSession($id, $data)
    {
        return true;
    }

    /**
     * Session destroy handler.
     *
     * This method should be overridden if {@link useCustomStorage} is set true.
     *
     * Do not call this method directly.
     *
     * @param string   $id  session ID
     *
     * @return boolean whether session is destroyed successfully
     */
    public function destroySession($id)
    {
        return true;
    }

    /**
     * Session GC (garbage collection) handler.
     *
     * This method should be overridden if {@link useCustomStorage} is set true.
     *
     * Do not call this method directly.
     *
     * @param integer  $maxLifetime the number of seconds after which data will be seen as 'garbage' and cleaned up.
     *
     * @return boolean whether session is GCed successfully
     */
    public function gcSession($maxLifetime)
    {
        return true;
    }

    /**
     * This method is required by the interface ArrayAccess.
     *
     * @param  mixed  $offset the offset to check on
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->sessionData[$offset]);
    }

    /**
     * This method is required by the interface ArrayAccess.
     *
     * @param  integer $offset the offset to retrieve element.
     *
     * @return mixed   the element at the offset, null if no element is found at the offset
     */
    public function offsetGet($offset)
    {
        return isset($this->sessionData[$offset]) ? $this->sessionData[$offset] : null;
    }

    /**
     * This method is required by the interface ArrayAccess.
     *
     * @param integer $offset the offset to set element
     *
     * @param mixed   $item the element value
     */
    public function offsetSet($offset, $item)
    {
        $this->sessionData[$offset] = $item;
    }

    /**
     * This method is required by the interface ArrayAccess.
     *
     * @param mixed $offset the offset to unset element
     */
    public function offsetUnset($offset)
    {
        unset($this->sessionData[$offset]);
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
