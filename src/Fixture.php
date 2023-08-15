<?php /** @noinspection PhpUnused */

namespace Arnolem\MagicFixtures;

use Arnolem\MagicFixtures\Exception\ServiceNotFoundException;
use Arnolem\MagicFixtures\Interface\FixtureInterface;
use Faker\Generator as Faker;
use Psr\Container\ContainerInterface;
use RuntimeException;

class Fixture implements FixtureInterface
{
    protected ContainerInterface $container;
    protected Faker $faker;
    private ObjectStorage $objectStorage;

    public function setUp(): void
    {
        // Nothing on parent
    }

    public function getFaker(): Faker
    {
        return $this->faker;
    }

    public function setFaker(Faker $faker): void
    {
        $this->faker = $faker;
    }

    /**
     * Retrieve a service from container (just a shortcut)
     */
    public function getService(string $id)
    {
        if ( ! $this->getContainer()->has($id)) {
            throw new ServiceNotFoundException($id);
        }

        return $this->getContainer()->get($id);
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function addReference(object $object, string $key, ?array $tags = null): void
    {
        $this->objectStorage->add($object, $key, $tags);
    }

    public function getReference(string $className, $key): object
    {
        return $this->objectStorage->get($className, $key);
    }

    public function getRandomReference(string $className, ?array $tags = null): object
    {
        return $this->objectStorage->findRandom($className, $tags);
    }

    public function getAllReference(string $className, ?array $tags = null): array
    {
        return $this->objectStorage->find($className, $tags);
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