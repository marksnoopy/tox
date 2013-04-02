<?php
/**
 * Represents as a advanced SQL for improved PDOes.
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
 * @package    Tox
 * @subpackage Tox\Data\Pdo
 * @author     Snakevil Zen <zsnakevil@gmail.com>
 * @copyright  © 2012 szen.in
 * @license    http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Data\Pdo;

use Tox\Core;

class Sql extends Core\Assembly implements ISql
{
    const TYPE_READ = 'read';

    const TYPE_WRITE = 'write';

    const RE_PARTITION = '@^(`?)\$(\w+)\1$@';

    protected $formatedSql;

    protected $indexes;

    protected $partitions;

    protected $sql;

    protected $tokens;

    protected $type;

    protected function analyse()
    {
        $this->tokens = $a_tokens = array();
        $b_escaped = $b_lob = $b_field = FALSE;
        $s_token = '';
        $i_token = $i_tokens = 0;
        for ($ii = 0, $jj = strlen($this->sql); $ii < $jj; $ii++)
        {
            if ($b_escaped)
            {
                $b_escaped = FALSE;
                $s_token .= '\\' . $this->sql[$ii];
                $i_token += 2;
                continue;
            }
            if ($b_lob)
            {
                if ('\'' == $this->sql[$ii])
                {
                    $b_lob = FALSE;
                    $a_tokens[] = $s_token . '\'';
                    $i_tokens++;
                    $s_token = '';
                    $i_token = 0;
                }
                else
                {
                    $s_token .= $this->sql[$ii];
                    $i_token++;
                }
                continue;
            }
            if ($b_field)
            {
                if ('`' == $this->sql[$ii])
                {
                    $b_field = FALSE;
                    $a_tokens[] = $s_token . '`';
                    $i_tokens++;
                    $s_token = '';
                    $i_token = 0;
                }
                else
                {
                    $s_token .= $this->sql[$ii];
                    $i_token++;
                }
                continue;
            }
            switch ($this->sql[$ii])
            {
                case '\\':
                    $b_escaped = TRUE;
                    break;
                case '\'':
                    $b_lob = TRUE;
                    if ($i_token)
                    {
                        $a_tokens[] = $s_token;
                        $i_tokens++;
                    }
                    $s_token = '\'';
                    $i_token = 1;
                    break;
                case '`':
                    $b_field = TRUE;
                    if ($i_token)
                    {
                        $a_tokens[] = $s_token;
                        $i_tokens++;
                    }
                    $s_token = '`';
                    $i_token = 1;
                    break;
                case '.':
                case '(':
                case ')':
                    if ($i_token)
                    {
                        $a_tokens[] = $s_token;
                        $i_tokens++;
                        $s_token = '';
                        $i_token = 0;
                    }
                    $a_tokens[] = $this->sql[$ii];
                    $i_tokens++;
                    break;
                case ' ':
                case "\n":
                case "\t":
                    if ($i_token)
                    {
                        $a_tokens[] = $s_token;
                        $i_token++;
                        $s_token = '';
                        $i_token = 0;
                    }
                    break;
                case ';':
                    if ($i_token)
                    {
                        $a_tokens[] = $s_token;
                        $i_token++;
                        $s_token = '';
                        $i_token = 0;
                    }
                    if ($i_tokens)
                    {
                        $this->tokens[] = $a_tokens;
                        $a_tokens = array();
                        $i_token =
                        $i_tokens = 0;
                        $s_token = '';
                    }
                    break;
                default:
                    $s_token .= $this->sql[$ii];
                    $i_token++;
            }
        }
        if (empty($this->tokens))
        {
            throw new EmptyStatementException;
        }
        return $this;
    }

    public function __construct($sql)
    {
        settype($sql, 'string');
        $this->formatedSql = '';
        $this->indexes =
        $this->partitions = array();
        $this->sql = trim($sql) . ';';
        $this->type = static::TYPE_READ;
        $this->analyse()->interpretAll();
    }

    protected function __getType()
    {
        return $this->type;
    }

    protected function identify($tokens, Array $index, $partition)
    {
        return is_array($tokens) ? $this->identify($tokens[array_shift($index)], $index, $partition) : $partition;
    }

    public function identifyPartitions(IPartition $domain, $partitions)
    {
        settype($partitions, 'array');
        $this->formatedSql = '';
        foreach ($this->partitions as $s_table => $s_part)
        {
            $this->partitions[$s_table] = array_key_exists($s_table, $partitions)
                ? $domain->identifyPartition($s_table, $partitions[$s_table])
                : $s_table;
        }
        return $this;
    }

    protected function interpret(Array $tokens, Array $index)
    {
        $s_type = static::TYPE_READ;
        $a_parts = array();
        $a_indexes = array();
        if (!empty($tokens))
        {
            $s_cmd = '';
            if (!is_array($tokens[0]))
            {
                $s_cmd = strtolower(array_shift($tokens));
                $s_type = static::TYPE_WRITE;
            }
            $a_keys = array();
            switch ($s_cmd)
            {
                case 'select':
                    $a_keys = array(array('from'), array('where'), array('from', 'join', ',', '.'));
                case 'show':
                    $s_type = static::TYPE_READ;
                    break;
                case 'delete':
                    $a_keys = array(array('from'), array('where'), array('from', '.'));
                    break;
                case 'insert':
                    $a_keys = array(array('insert'), array('set', 'values'), array('insert', 'into', '.'));
                    break;
                case 'replace':
                    $a_keys = array(array('replace'), array('set', 'values'), array('replace', 'into', '.'));
                    break;
                case 'update':
                    $a_keys = array(array('update'), array('set'), array('update', '.'));
                    break;
            }
            if (!empty($a_keys))
            {
                $b_region = $b_point = FALSE;
                for ($ii = 0, $jj = count($tokens); $ii < $jj; $ii++)
                {
                    $kk = strtolower($tokens[$ii]);
                    if (!$b_region)
                    {
                        if (!in_array($kk, $a_keys[0]))
                        {
                            continue;
                        }
                        $b_region = TRUE;
                    }
                    if (in_array($kk, $a_keys[1]))
                    {
                        break;
                    }
                    if ($b_point)
                    {
                        $b_point = FALSE;
                        if (preg_match(static::RE_PARTITION, $tokens[$ii], $ll))
                        {
                            $a_parts[$ll[2]] = $ll[2];
                            if (array_key_exists($ll[2], $a_indexes))
                            {
                                $a_indexes[$ll[2]] = array();
                            }
                            $a_index = $index;
                            $a_index[] = $ii;
                            $a_indexes[$ll[2]][] = $a_index;
                        }
                        continue;
                    }
                    if (in_array($kk, $a_keys[2]))
                    {
                        $b_point = TRUE;
                    }
                }
            }
        }
        return array($s_type, $a_parts, $a_indexes);
    }

    protected function interpretAll()
    {
        // TODO support subqueries
        for ($ii = 0, $jj = count($this->tokens); $ii < $jj; $ii++)
        {
            list($s_type, $a_parts, $a_indexes) = $this->interpret($this->tokens[$ii], array($ii));
            if (static::TYPE_WRITE != $this->type && static::TYPE_WRITE == $s_type)
            {
                $this->type = $s_type;
            }
            $this->partitions = array_merge($this->partitions, $a_parts);
            foreach ($a_indexes as $s_table => $a_index)
            {
                $this->indexes[$s_table] = array_key_exists($s_table, $this->indexes)
                    ? array_merge($this->indexes[$s_table], $a_index)
                    : $a_index;
            }
        }
        return $this;
    }

    public static function parse($statement)
    {
        if (!$statement instanceof static)
        {
            $statement = new static($statement);
        }
        return $statement;
    }

    public function __toString()
    {
        if (!strlen($this->formatedSql))
        {
            $a_tokens = $this->tokens;
            reset($this->partitions);
            for ($ii = 0, $jj = count($this->partitions); $ii < $jj; $ii++)
            {
                list($s_table, $s_part) = each($this->partitions);
                for ($kk = 0, $ll = count($this->indexes[$s_table]); $kk < $ll; $kk++)
                {
                    $a_tokens = $this->identify($a_tokens, $this->indexes[$s_table][$kk], $s_part);
                }
            }
            $a_stmts = array();
            for ($ii = 0, $jj = count($a_tokens); $ii < $jj; $ii++)
            {
            	for ($kk = 0, $ll = count($a_tokens[$ii]); $kk < $ll; $kk++)
            	{
            		switch ($a_tokens[$ii][$kk])
            		{
            			case '(':
            			case '.':
            				$a_tokens[$ii][$kk] = "\x01\x02{$a_tokens[$ii][$kk]}\x03\x04";
            				break;
            		}
            	}
	            $a_stmts[] = str_replace(array("\x01\x02", "\x03\x04"), array(),
                	str_replace(array(" \x01\x02(\x03\x04", " \x01\x02.\x03\x04 "),
	                	array('(', '.'),
	                	implode(' ', $a_tokens[$ii])
	                )
                );
            }
            $this->formatedSql = implode('; ', $a_stmts);
        }
        return $this->formatedSql;
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
