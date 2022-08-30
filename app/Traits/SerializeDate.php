<?php

namespace app\Traits;

use DateTimeInterface;

trait SerializeDate
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d h:i:s');
    }
}
