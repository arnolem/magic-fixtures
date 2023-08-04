<?php

namespace Arnolem\MagicFixtures\Interface;

use Psr\Container\ContainerInterface;

interface FixtureInterface
{
    public function setContainer(ContainerInterface $container): void;

    public function setUp(): void;

    public function execute(): void;

    public function needs(): array;
}
