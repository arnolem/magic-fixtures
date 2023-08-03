<?php

namespace Arnolem\MagicFixtures\Interface;

use Psr\Container\ContainerInterface;

interface FixtureInterface
{
    public static function setContainer(ContainerInterface $container): void;

    public function execute(): void;

    public function needs(): array;
}
