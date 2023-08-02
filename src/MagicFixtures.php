<?php

namespace Arnolem\MagicFixtures;

use Arnolem\MagicFixtures\Exception\ClassNotFixture;
use Arnolem\MagicFixtures\Exception\DirectoryNotExist;
use Arnolem\MagicFixtures\Interface\Fixture;
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

    public function __construct(
        readonly private ContainerInterface $container
    ) {
    }


    public function loadFromDirectory(string $path): void
    {
        if (!is_dir($path)) {
            throw new DirectoryNotExist($path);
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
            if (!$reflexion->implementsInterface(Fixture::class)) {
                continue;
            }

            // Continue if not instantiable
            if (!$reflexion->isInstantiable()) {
                continue;
            }

            // Continue if not a included fixtures
            $sourceFile = $reflexion->getFileName();
            if (!in_array($sourceFile, $includedFiles, true)) {
                continue;
            }

            $this->fixtures[] = new $class($this->container);
        }
    }

    public function execute(): void
    {
        $fixtures = $this->getOrderedFixtures();

        foreach ($fixtures as $fixture) {
            $fixture->execute();
        }
    }

    /**
     * @return array<Fixture>
     * @throws ReflectionException
     * @todo : Throw an exception for infinite loop (circular reference)
     */
    private function getOrderedFixtures(): array
    {
        $orderedFixtures = [];

        // add recursively fixture in order
        $resolve = static function (string $fixtureName) use (&$resolve, &$orderedFixtures) {

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
            if (!$reflexion->implementsInterface(Fixture::class)) {
                throw new ClassNotFixture($fixtureName);
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
                $resolve($needFixtureName);
            }

            // Mark this fixture resolved
            $orderedFixtures[] = $fixtureName;

        };

        /** @var Fixture $fixture */
        foreach ($this->fixtures as $fixture) {
            // Apply resolver on each fixture to ordered
            $resolve(get_class($fixture));
        }

        return $this->fixtures;
    }
}
