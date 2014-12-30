<?php
// TODO: Rename this bitch and move to utils

namespace Vreasy\Models;

class Collection extends \ArrayObject implements \JsonSerializable, \Serializable
{
    public $field;
    public $classType;
    public $serializeAsProxy = false;

    public function __construct($field, $classType, $input = [])
    {
        parent::__construct($input);
        $this->field = $field;
        if ($input) {
            $classType = static::getClassTypeOf($input);
        }
        $this->classType = $classType;
    }

    public function offsetGet($i = null)
    {
        if (!$i) {
            $i = 0;
        }
        return parent::offsetGet($i);
    }

    public function jsonSerialize()
    {
        $json = [];
        foreach ($this as $value) {
            $json[] = $value;
        }
        return $json;
    }

    public function isEmpty()
    {
         return !$this->isPresent();
    }

    public function isPresent()
    {
         return !!$this->count();
    }

    public static function getClassTypeOf($storage)
    {
        if ($storage instanceof Collection) {
            if (($it = $storage->getIterator()) && $it->valid()) {
                return static::getClassTypeOf($it->current());
            }
        } elseif (is_array($storage) && $storage) {
            return static::getClassTypeOf(array_shift($storage));
        } else {
            return get_class($storage);
        }
    }

    public function __toString()
    {
        // Some array methods need to convert the values to strings for comparition
        return spl_object_hash($this);
    }

    public function __clone()
    {
        $clones = [];
        foreach ($this as $value) {
            $clones[] = clone $value;
        }
        $this->exchangeArray($clones);
    }
}
