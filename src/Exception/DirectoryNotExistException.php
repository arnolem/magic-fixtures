<?php

namespace Arnolem\MagicFixtures\Exception;

use InvalidArgumentException;

class DirectoryNotExistException extends InvalidArgumentException
{
    public function __construct(string $path)
    {
        parent::__construct('"' . $path . '" does not exist');
    }
}
