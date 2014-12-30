<?php

namespace Vreasy\Models;

use Vreasy\Exceptions\UnsupportedMethodException;
use Vreasy\Models\Collection;
use Vreasy\NullObject;

class One extends Collection implements \JsonSerializable
{
    private $_null;

    public function __construct($field, $classType, $input = null)
    {
        if ($input) {
            parent::__construct($field, $classType, [$input]);
        } else {
            parent::__construct($field, $classType);
        }
        // Initialized once so to matain equallity when returning from getStorage
        $this->_null = new NullObject;
    }

    public function getAssociation()
    {
        return $this->getStorage();
    }

    public function buildAssociation($params = [])
    {
        $association = Base::instanceWith($params, $this->classType);
        $this->exchangeArray([$association]);
        return $association;
    }

    protected function getStorage()
    {
        if ($this->isPresent() && ($it = $this->getIterator()) && $it->valid()) {
            return $it->current();
        } else {
            return $this->_null;
        }
    }

    public function jsonSerialize()
    {
        return $this->getStorage();
    }

    public function append($value)
    {
        if ($this->count() > 0) {
            throw new UnsupportedMethodException('One can only have one '.$this->classType);
        } else {
            parent::append($value);
        }
    }

    public function offsetSet($i, $v)
    {
        if ($this->count() > 0) {
            throw new UnsupportedMethodException('One can only have one '.$this->classType);
        } else {
            parent::offsetSet($i, $v);
        }
    }

    public function offsetGet($i)
    {
        return $this->getStorage();
    }

    public function __set($attribute, $value)
    {
        return $this->getStorage()->$attribute = $value;
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->getStorage(), $method], $args);
    }

    public function __get($attribute)
    {
        return $this->getStorage()->$attribute;
    }

    public function __isset($attribute)
    {
        return isset($this->getStorage()->$attribute);
    }

    public function __unset($attribute)
    {
        unset($this->getStorage()->$attribute);
    }
}
