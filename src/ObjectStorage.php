<?php

namespace Arnolem\MagicFixtures;

use Arnolem\MagicFixtures\ObjectStorage\Item;
use ArrayObject;
use function in_array;

class ObjectStorage
{
    /** @var array<ArrayObject> */
    private array $storageByClassName;
    /** @var array<ArrayObject> */
    private array $storageByTag;

    public function __construct()
    {
        $this->storageByClassName = [];
        $this->storageByTag       = [];
    }

    public function add(object $object, string $key, array $tags = null): void
    {
        // Create an ObjectStorage\Item
        $item = Item::create($object, $key, $tags);

        // ClassName Storage
        $this->indexByClassName($item);

        // Tags Storage
        $this->indexByTags($item);
    }

    private function indexByClassName(Item $item): void
    {
        $classNameStorage = $this->getClassNameStorage($item->getClassName());
        $classNameStorage->offsetSet($item->getKey(), $item);
    }

    private function getClassNameStorage(string $class): ArrayObject
    {
        if ( ! (isset($this->storageByClassName[$class]) && $this->storageByClassName[$class] instanceof ArrayObject)) {
            $this->storageByClassName[$class] = new ArrayObject();
        }

        return $this->storageByClassName[$class];
    }

    private function indexByTags(Item $item): void
    {
        $tagStorage = $this->getTagStorage($item->getClassName());
        $tagStorage->offsetSet($item->getKey(), $item);
    }

    private function getTagStorage(string $class): ArrayObject
    {
        if ( ! (isset($this->storageByTag[$class]) && $this->storageByTag[$class] instanceof ArrayObject)) {
            $this->storageByTag[$class] = new ArrayObject();
        }

        return $this->storageByTag[$class];
    }

    public function get(string $className, $key): object
    {
        return $this->getItem($className, $key)->getObject();
    }

    private function getItem(string $className, $key): Item
    {
        return $this->getClassNameStorage($className)->offsetGet($key);
    }

    public function findRandom(string $className, ?array $tags): object
    {
        $objectList = $this->find($className, $tags);

        return $objectList[array_rand($objectList)];
    }

    public function find(string $className, ?array $tags): array
    {
        $itemList = [];

        $classNameStorage = $this->getClassNameStorage($className);

        /** @var Item $item */
        foreach ($classNameStorage->getIterator() as $item) {
            $itemList[$item->getKey()] = $item->getObject();
        }

        if (isset($tags) && count($tags) > 0) {
            // Todo : optimize the algorithme with the new $item->getTags() methods
            foreach ($tags as $tag) {
                $tagStorage = $this->getClassNameStorage($tag);

                $hasTag = false;
                /** @var Item $item */
                foreach ($tagStorage->getIterator() as $item) {
                    if (in_array($item->getObject(), $itemList, true)) {
                        $hasTag = true;
                        break;
                    }
                }
                if ( ! $hasTag) {
                    unset($itemList[$item->getKey()]);
                }
            }
        }

        return $itemList;
    }

}