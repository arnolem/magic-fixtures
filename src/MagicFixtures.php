<?php /** @noinspection PhpUnused */

namespace Arnolem\MagicFixtures;

use Arnolem\MagicFixtures\Exception\ClassNotFixtureException;
use Arnolem\MagicFixtures\Exception\DirectoryNotExistException;
use Arnolem\MagicFixtures\Interface\FixtureInterface;
use Faker\Factory;
use Faker\Generator;
use Iterator;
use Psr\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;
use function get_class;
use function in_array;

class MagicFixtures
{
    private array $fixtures = [];
    private Generator $faker;
    private ObjectStorage $objectStorage;
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->container     = $container;
        $this->faker         = Factory::create('fr_FR');
        $this->objectStorage = new ObjectStorage();
    }

    public function loadFromDirectory(string $path): void
    {
        if ( ! is_dir($path)) {
            throw new DirectoryNotExistException($path);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        $this->loadFromIterator($iterator);

    }

    private function loadFromIterator(Iterator $iterator): void
    {
        $includedFiles = [];

        /** @var RecursiveDirectoryIterator $file */
        foreach ($iterator as $file) {

            // Ignore non-php files
            $filename = $file->getBasename('.php');
            if ($filename === $file->getBasename()) {
                continue;
            }

            // Load source file
            $sourceFile = realpath($file->getPathName());
            require_once $sourceFile;
            $includedFiles[] = $sourceFile;
        }

        // List all declared classes
        $declared = get_declared_classes();
        sort($declared);

        foreach ($declared as $class) {

            try {
                $reflexion = new ReflectionClass($class);
            } catch (ReflectionException) {
                continue;
            }

            // Continue if is not a Fixture
            if ( ! $reflexion->implementsInterface(FixtureInterface::class)) {
                continue;
            }

            // Continue if not instantiable
            if ( ! $reflexion->isInstantiable()) {
                continue;
            }

            // Continue if not a included fixtures
            $sourceFile = $reflexion->getFileName();
            if ( ! in_array($sourceFile, $includedFiles, true)) {
                continue;
            }

            /** @var Fixture $object */
            $object = new $class;
            $object->setFaker($this->faker);
            $object->setObjectStorage($this->objectStorage);
            $object->setContainer($this->container);

            $this->fixtures[$class] = $object;
        }
    }

    public function execute(): void
    {
        $fixtures = $this->getOrderedFixtures();

        /** @var FixtureInterface $fixture */
        foreach ($fixtures as $fixture) {
            $fixture->setUp();
            $fixture->execute();
        }
    }

    /**
     * @return array
     * @throws ReflectionException
     * @todo : Throw an exception for infinite loop (circular reference)
     */
    private function getOrderedFixtures(): array
    {
        $orderedFixtures = [];

        /** @var Fixture $fixture */
        foreach ($this->fixtures as $fixture) {
            // Apply resolver on each fixture to ordered
            self::resolveNeeds(get_class($fixture), $orderedFixtures);
        }

        $fixtures = [];
        foreach ($orderedFixtures as $fixtureName) {
            $fixtures[] = $this->fixtures[$fixtureName];
        }

        return $fixtures;
    }

    public static function resolveNeeds($fixtureName, &$orderedFixtures): void
    {
        // Return if this fixture is resolved
        if (in_array($fixtureName, $orderedFixtures, true)) {
            return;
        }

        try {
            $reflexion = new ReflectionClass($fixtureName);
        } catch (ReflectionException) {
            return;
        }

        // Error if is not a Fixture
        if ( ! $reflexion->implementsInterface(FixtureInterface::class)) {
            throw new ClassNotFixtureException($fixtureName);
        }

        // Get needFixture for this fixture
        if ($reflexion->hasMethod('needs')) {
            $needs = $reflexion->newInstanceWithoutConstructor()->needs();
        } else {
            $needs = [];
        }

        foreach ($needs as $needFixtureName) {
            // Return if need fixture is resolved
            if (in_array($needFixtureName, $orderedFixtures, true)) {
                continue;
            }
            // Resolve need fixture recursively
            self::resolveNeeds($needFixtureName, $orderedFixtures);
        }

        // Mark this fixture resolved
        $orderedFixtures[] = $fixtureName;
    }

    public function getObjectStorage(): ObjectStorage
    {
        return $this->objectStorage;
    }
}
