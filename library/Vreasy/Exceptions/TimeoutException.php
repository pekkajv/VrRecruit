<?php
namespace Vreasy\Exceptions;

class TimeoutException extends \RuntimeException {
    protected $timeout;

    public function setTimeout($value)
    {
        $this->timeout = $value;
    }
}
