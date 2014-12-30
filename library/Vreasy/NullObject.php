<?php

namespace Vreasy;

class NullObject
{
    public function __set($attribute, $value)
    {
        return;
    }

    public function __call($method, $args)
    {
        return;
    }

    public function __get($attribute)
    {
        return;
    }

    public function __isset($attribute)
    {
        return false;
    }

    public function __unset($attribute)
    {
        return;
    }
}
