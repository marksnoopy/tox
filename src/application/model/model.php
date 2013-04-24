<?php
/**
 * Defines the models.
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
 * @copyright © 2012-2013 PHP-Tox.org
 * @license   GNU General Public License, version 3
 */

namespace Tox\Application\Model;

use Tox\Core;
use Tox\Application;

use Exception as PHPException;

/**
 * Represents as a model.
 *
 * **THIS CLASS CANNOT BE INSTANTIATED.**
 *
 * __*ALIAS*__ as `Tox\Application\Model`.
 *
 * @package tox.application.model
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
 */
abstract class Model extends Core\Assembly implements Application\IModel
{
    /**
     * Retrieves the unique indentifier.
     *
     * @var string
     */
    protected $id;

    /**
     * Stores the loaded model entities.
     *
     * @var array
     */
    protected static $instances = array();

    /**
     * Stores the data access object in use.
     *
     * @var Application\IDao
     */
    protected $dao;

    /**
     * Stores changed attributes values.
     *
     * @var mixed[]
     */
    protected $toxStash;

    /**
     * Stores the original attributes values.
     *
     * @var mixed[]
     */
    protected $toxOriginal;

    /**
     * Stores whether in async mode.
     *
     * @var bool
     */
    protected $toxAsync;

    /**
     * Retrieves the default data access object.
     *
     * @return Application\IDao
     */
    abstract protected function getDefaultDao();

    /**
     * CONSTRUCT FUNCTION
     */
    protected function __construct()
    {
        $this->toxStash =
        $this->toxOriginal = array();
        $this->toxAsync = true;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the identitifer
     *
     * @param  mixed $value New identifier.
     * @return void
     *
     * @throws IdentifierReadOnlyException If changing the indentifier of an
     *                                     existant model entity.
     */
    public function setId($value)
    {
        if ($this->isAlive() || isset($this->toxOriginal['id'])) {
            throw new IdentifierReadOnlyException(array('id' => $this->id));
        }
        $this->toxStash['id'] = (string) $value;
    }

    /**
     * Be invoked on retrieving the identifier.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @return string
     */
    final protected function toxGetId()
    {
        return $this->getId();
    }

    /**
     * Be invoked on setting the identifier.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @param  mixed $value New identifier.
     * @return void
     *
     * @throws IdentifierReadOnlyException
     */
    final protected function toxSetId($value)
    {
        $this->setId($value);
    }

    /**
     * Retrieves the data access object in use.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return Application\IDao
     */
    final protected function getDao()
    {
        if (!$this->dao instanceof Application\IDao) {
            $this->dao = $this->getDefaultDao();
        }
        return $this->dao;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  string $id  The unique identifier of the model entity.
     * @param  IDao   $dao OPTIONAL. Data access object in use.
     * @return self
     *
     * @throws NonExistantEntityException If the model entity does not exist.
     */
    final public static function load($id, Application\IDao $dao = null)
    {
        $s_type = get_called_class();
        $id = (string) $id;
        if (!isset(self::$instances[$s_type])) {
            self::$instances[$s_type] = array();
        } elseif (isset(self::$instances[$s_type][$id])) {
            return self::$instances[$s_type][$id];
        }
        $o_mod = static::newModel();
        $o_mod->id = $id;
        $o_mod->dao = $dao;
        try {
            $a_fields = $o_mod->getDao()->read($id);
            if (empty($a_fields)) {
                throw new PHPException;
            }
        } catch (PHPException $ex) {
            throw new NonExistantEntityException(array('id' => $id));
        }
        self::$instances[$s_type][$id] = $o_mod->assign($a_fields);
        return $o_mod;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Remained for forward compatibility. Would be removed in some
     *             future version.
     *
     * @param  string $id  The unique identifier of the model entity.
     * @param  IDao   $dao OPTIONAL. Data access object in use.
     * @return self
     */
    final public static function setUp($id, Application\IDao $dao = null)
    {
        return static::load($id, $dao);
    }

    /**
     * Creates a new model object.
     *
     * @return self
     *
     * @codeCoverageIgnore
     */
    protected static function newModel()
    {
        return new static;
    }

    /**
     * Assigns attributes.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  mixed[] $attributes Attributes values.
     * @return self
     */
    final protected function assign($attributes)
    {
        $a_props = $this->toxGetMagicProps();
        foreach ($attributes as $ii => $jj) {
            if (!isset($a_props[$ii]) || self::TOX_PROPERTY_DENIED == $a_props[$ii] || 'dao' == $ii) {
                continue;
            } elseif ($this->isAlive()) {
                $this->$ii = $jj;
                $this->toxOriginal[$ii] = $jj;
            } else {
                $this->toxStash[$ii] = $jj;
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return bool
     */
    final public function isAlive()
    {
        return !is_null($this->id);
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return bool
     */
    final public function isChanged()
    {
        return !empty($this->toxStash);
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @param  string $prop Set attribute.
     * @return void
     */
    final protected function toxPostSet($prop)
    {
        $prop = (string) $prop;
        if ('id' == $prop ||
            !$this->toxIsMagicPropWritable($prop) ||
            !isset($this->toxOriginal[$prop]) ||
            $this->$prop == $this->toxOriginal[$prop]
        ) {
            return;
        }
        $this->toxStash[$prop] = $this->$prop;
        $this->$prop = $this->toxOriginal[$prop];
        if (!$this->toxAsync) {
            $this->commit();
        }
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  mixed[]          $attributes Attributes of the new model entity.
     * @param  Application\IDao $dao        OPTIONAL. Data access object in use.
     * @return self
     */
    final public static function prepare($attributes, Application\IDao $dao = null)
    {
        $o_mod = static::newModel();
        $o_mod->dao = $dao;
        return $o_mod->assign($attributes);
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return self
     */
    final public function terminate()
    {
        $this->toxStash = array('id' => null);
        return $this->toxAsync ? $this : $this->commit();
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return self
     *
     * @throws DuplicateIdentifierException If the identifier already in use.
     */
    final public function commit()
    {
        if ($this->isAlive()) {
            if (array_key_exists('id', $this->toxStash) && is_null($this->toxStash['id'])) {
                $this->getDao()->delete($this->id);
                $this->id = null;
            } elseif ($this->isChanged()) {
                $this->getDao()->update($this->id, $this->toxStash);
                $this->assign($this->toxStash);
            }
            return $this->reset();
        }
        $this->id = $this->toxOriginal['id'] = $this->getDao()->create($this->toxStash);
        $s_type = get_class($this);
        if (isset(self::$instances[$s_type][$this->id])) {
            throw new DuplicateIdentifierException(array('prototype' => $s_type, 'id' => $this->id));
        }
        self::$instances[$s_type][$this->id] = $this;
        unset($this->toxStash['id']);
        return $this->assign($this->toxStash)->reset();
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return self
     *
     * @throws PreparationCanNotResetException If on resetting a prepared model
     *                                         entity.
     */
    final public function reset()
    {
        if (!$this->isAlive() && !array_key_exists('id', $this->toxOriginal)) {
            throw new PreparationCanNotResetException;
        }
        $this->toxStash = array();
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return string
     */
    final public function __toString()
    {
        return (string) $this->id;
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function __clone()
    {
        $this->toxOriginal = array();
        foreach ($this->toxGetMagicProps() as $ii => $jj) {
            if ('dao' == $ii || self::TOX_PROPERTY_DENIED == $jj) {
                continue;
            }
            if (!is_null($this->$ii)) {
                $this->toxStash[$ii] = $this->$ii;
                $this->$ii = null;
            }
        }
        $this->toxStash['id'] = null;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return bool
     */
    final public function isAsync()
    {
        return $this->toxAsync;
    }

    /**
     * {@inhertdoc}
     *
     * @return self
     */
    public function enableAsync()
    {
        $this->toxAsync = true;
        return $this;
    }

    /**
     * {@inhertdoc}
     *
     * @return self
     */
    public function disableAsync()
    {
        $this->toxAsync = false;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  Application\IModelSet $set The container models set.
     * @param  Application\IDao      $dao Data access object to use.
     * @return self
     */
    final public static function import(Application\IModelSet $set, Application\IDao $dao)
    {
        $m_cur = $set->current();
        if ($m_cur instanceof Application\IModel) {
            return $m_cur;
        } elseif (!isset($m_cur['id'])) {
            throw new IllegalSetElementException;
        }
        $s_type = get_called_class();
        if (!isset(self::$instances[$s_type])) {
            self::$instances[$s_type] = array();
        }
        if (isset(self::$instances[$s_type][$m_cur['id']])) {
            $o_mod = self::$instances[$s_type][$m_cur['id']];
        } else {
            $o_mod = static::newModel();
            $o_mod->id = $m_cur['id'];
            $o_mod->dao = $dao;
            self::$instances[$s_type][$m_cur['id']] = $o_mod;
        }
        return $o_mod->assign($m_cur);
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
