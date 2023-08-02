<?php

namespace Arnolem\MagicFixtures\Exception;

use InvalidArgumentException;

class DirectoryNotExist extends InvalidArgumentException
{
    public function __construct(string $path)
    {
        parent::__construct('"' . $path . '" does not exist');
    }
}
