<?php

namespace Arnolem\MagicFixtures\ObjectStorage;

use function get_class;

final readonly class Item
{
    private function __construct(
        private object $object,
        private string $key,
        private array $tags
    ) {
    }

    public static function create(object $object, string $key, ?array $tags = null): Item
    {
        return new self($object, $key, $tags ?? []);
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function getClassName(): string
    {
        return get_class($this->object);
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}