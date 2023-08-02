<?php

namespace Arnolem\MagicFixtures\Interface;

use Psr\Container\ContainerInterface;

interface Fixture
{

    public function __construct(ContainerInterface $container);
    public function execute(): void;
}
