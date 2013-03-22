<?php
/**
 * Defines  memcache configruation .
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
 * @copyright © 2012-2013 SZen.in
 * @license   GNU General Public License, version 3
 */

namespace Tox\Data\KV;

/**
 * Represents the config of the memcache .
 *
 * @package tox.data.kv
 * @author  Qiang Fu <fuqiang007enter@gmail.com>
 */
class MemCacheServerConfiguration
{
    /**
     * Memcache server hostname or IP address.
     * 
     * @var string 
     */
    public $host;

    /**
     * Memcache server port.
     * 
     * @var integer 
     */
    public $port;

    /**
     * Whether to use a persistent connection.
     * 
     * @var boolean 
     */
    public $persistent;

    /**
     * Probability of using this server among all servers.
     * 
     * @var integer 
     */
    public $weight;

    /**
     * value in seconds which will be used for connecting to the server.
     * 
     * @var integer 
     */
    public $timeout;

    /**
     * How often a failed server will be retried (in seconds)
     * 
     * @var integer 
     */
    public $retryInterval;

    /**
     * If the server should be flagged as online upon a failure.
     * 
     * @var boolean 
     */
    public $status;

    /**
     * Constructor.
     * 
     * @param  array $config list of memcache server configurations.
     * @throws               KV\MemcacheConfigNotArrayException if the configuration is not an array
     */
    public function __construct($config)
    {

        $this->persistent = true;
        $this->port = 11211;
        $this->weight = 1;
        $this->timeout = 15;
        $this->retryInterval = 15;
        $this->status = true;


        if (is_array($config)) {
            foreach ($config as $key => $value)
                $this->$key = $value;
            if ($this->host === null)
                throw new EmptyHostException(array('host' => $c));
        }
        else {
            throw new MemcacheConfigNotArrayException(array('config' => $config));
        }
    }

}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120