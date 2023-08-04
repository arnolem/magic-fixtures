<?php

namespace Arnolem\MagicFixtures\ObjectStorage\Exception;

use InvalidArgumentException;
use function implode;

class NoObjectFoundException extends InvalidArgumentException
{
    public function __construct(string $className, ?array $tags)
    {
        if (isset($tags) && count($tags) > 0) {
            parent::__construct('No object found for "' . $className . '" class with tags : ' . implode(', ', $tags));
        }

        parent::__construct('No object found for "' . $className . '" class');

    }
}
