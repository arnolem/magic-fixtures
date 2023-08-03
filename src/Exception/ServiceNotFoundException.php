<?php

namespace Arnolem\MagicFixtures\Exception;

use RuntimeException;

class ServiceNotFoundException extends RuntimeException
{

    public function __construct(string $path)
    {
        parent::__construct('Unable to load service "' . $path . '"');
    }
}