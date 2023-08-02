<?php

namespace Arnolem\MagicFixtures\Exception;

use InvalidArgumentException;

class ClassNotFixture extends InvalidArgumentException
{
    public function __construct(string $class)
    {
        parent::__construct('"' . $class . '" does not implement Interface\Fixture');
    }
}
