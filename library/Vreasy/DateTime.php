<?php
namespace Vreasy;

class DateTime extends \DateTime
{
    public function __construct(
        $time = "now",
        \DateTimeZone $timezone = null
    ) {
        parent::__construct($time, $timezone);
    }

    public function getHours()
    {
        return (int)$this->format('H');
    }

    public function getMinutes()
    {
        return (int)$this->format('i');
    }

    public function getSeconds()
    {
        return (int)$this->format('s');
    }
}
