<?php

namespace Vreasy\Models;

class Many extends Collection implements \JsonSerializable, \IteratorAggregate
{
    protected $isMarkedForDestruction = false;

    public function offsetUnset($i)
    {
        parent::offsetUnset($i);
        $this->reindexStorage();

    }

    public function offsetSet($index, $newval)
    {
        parent::offsetSet($index, $newval);
        $this->isMarkedForDestruction = false;
    }

    public function append($value)
    {
        parent::append($value);
        $this->isMarkedForDestruction = false;
    }

    private function reindexStorage()
    {
        $tmpArray = [];
        foreach ($this as $item) {
            $tmpArray[] = $item;
        }
        $this->exchangeArray($tmpArray);
    }

    public function getCollection()
    {
        return $this->getArrayCopy();
    }

    public function buildCollection($collection = [])
    {
        $this->exchangeArray([]);
        if (is_array($collection) || $collection instanceof \Traversable) {
            if (is_array($collection)) {
                foreach ($collection as $a) {
                    $this->appendAssociation($a);
                }
            }
        }
    }

    public function appendAssociation($params = [])
    {
        $association = Base::instanceWith($params, $this->classType);
        $this->append($association);
        return $association;
    }

    public function exchangeArray($array)
    {
        parent::exchangeArray($array);
        $this->isMarkedForDestruction = !$array;
    }

    public function markForDestruction()
    {
        $this->isMarkedForDestruction = true;
    }

    public function isMarkedForDestruction()
    {
        return $this->isMarkedForDestruction;
    }
}
