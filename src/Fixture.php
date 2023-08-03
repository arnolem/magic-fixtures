<?php /** @noinspection PhpUnused */

namespace Arnolem\MagicFixtures;

use Arnolem\MagicFixtures\Exception\ServiceNotFoundException;
use Arnolem\MagicFixtures\Interface\FixtureInterface;
use Faker\Generator as Faker;
use Psr\Container\ContainerInterface;
use RuntimeException;

class Fixture implements FixtureInterface
{
    protected static ContainerInterface $container;
    protected Faker $faker;
    private ObjectStorage $objectStorage;

    public function getFaker(): Faker
    {
        return $this->faker;
    }

    /**
     * Retrieve a service from container (just a shortcut)
     */
    public function getService(string $id)
    {
        if ( ! self::getContainer()->has($id)) {
            throw new ServiceNotFoundException($id);
        }

        return self::getContainer()->get($id);
    }

    public static function getContainer(): ContainerInterface
    {
        return self::$container;
    }

    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    public function addReference(object $object, string $key, array $tags = null): void
    {
        $this->objectStorage->add($object, $key, $tags);
    }

    public function getReference(string $className, $key): object
    {
        return $this->objectStorage->get($className, $key);
    }

    public function getRandomReference(string $className, array $tags = null): object
    {
        return $this->objectStorage->findRandom($className, $tags);
    }

    public function getAllReference(string $className, array $tags = null): array
    {
        return $this->objectStorage->find($className, $tags);
    }

    public function setFaker(Faker $faker): void
    {
        $this->faker = $faker;
    }

    public function execute(): void
    {
        throw new RuntimeException("The execute() method is not implemented");
    }

    public function needs(): array
    {
        return [];
    }

    public function setObjectStorage(ObjectStorage $objectStorage): void
    {
        $this->objectStorage = $objectStorage;
    }

}