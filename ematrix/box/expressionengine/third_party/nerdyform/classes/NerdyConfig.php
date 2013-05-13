<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Access the configuration as a tree
// Essentially turns some:param:name="value"
// into something similar to ['some' => ['param' => ['name' => value]]]
class NerdyConfig
implements IteratorAggregate
{
    public $_config = array();
    public static $_current_action;

    public function __construct($config = array())
    {
        $this->_config = $config;
    }

    public static function merge($a, $b)
    {
        if (is_null($a))
        {
            return $b;
        }
        elseif (is_null($b))
        {
            return $a;
        }
        else
        {
            if (is_object($a))
            {
                $array_a = $a->to_array();
            }
            else
            {
                $array_a = (array)$a;
            }
            
            if (is_object($b))
            {
                $array_b = $b->to_array();
            }
            else
            {
                $array_b = (array)$b;
            }
                        
            return new NerdyConfig(array_merge($array_a, $array_b));
        }
    }

    public function getAll()
    {
        return $this->_config;
    }

    public function toArray()
    {
        return $this->_config;
    }

    public function to_array()
    {
        return $this->_config;
    }

    public function get_array($keys)
    {
        $value = $this->get($keys);
        
        if ($value instanceof NerdyConfig)
        {
            return $value->to_array();
        }
        else
        {
            return (array)$value;
        }
    }

    public function _keys($keys)
    {
        return (array)$keys;
    }

    public function _ensure_sub($key)
    {
        if (isset($this->_config[$key])
            && $this->_config[$key] instanceof NerdyConfig)
        {
            return $this->_config[$key];
        }
        else
        {
            $sub = new NerdyConfig();
            $this->_config[$key] = $sub;
            return $sub;
        }
    }

    public function _split_keys($keys)
    {
        $keys = $this->_keys($keys);
        $first = array_shift($keys);
        $rest = $keys;

        return array($first, $rest);
    }

    public function set($keys, $value)
    {
        list($key, $rest) = $this->_split_keys($keys);

        if (0 == count($rest))
        {
            // This is the last key
            $this->_config[$key] = $value;
            return $value;
        }
        else
        {
            $sub = $this->_ensure_sub($key);
            $sub->set($rest, $value);
        }
    }

    public function delete($keys)
    {
        $this->set($keys, null);
    }

    public function exists($keys)
    {
        list($key, $rest) = $this->_split_keys($keys);

        if (!isset($this->_config[$key]))
        {
            return false;
        }
        elseif (0 == count($rest))
        {
            return isset($this->_config[$key]);
        }
        else
        {
            return $this->_config[$key]->isset($rest, $default);
        }
    }

    public function get($keys, $default = null)
    {
        list($key, $rest) = $this->_split_keys($keys);

        if (!isset($this->_config[$key]))
        {
            return $default;
        }
        elseif (0 == count($rest))
        {
            return $this->_config[$key];
        }
        else
        {
            return $this->_config[$key]->get($rest, $default);
        }
    }

    public function getIterator()
    {
        return new ArrayIterator($this->_config);
    }

    public function __toString()
    {
        return (string)$this->_config;
    }
}
