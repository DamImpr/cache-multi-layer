<?php

namespace CacheMultiLayer\Tests\Entity;

use CacheMultiLayer\Interface\Cacheable;
use Override;

/**
 * 
 * test class, used only for the phpunit test suite
 * 
 * @author Damiano Improta <code@damianoimprota.dev> 
 */
class Foo implements Cacheable {

    private ?int $x = null;

    private ?string $y = null;

    private array $z = [];

    private ?Foo $foo = null;

    public function getX(): int {
        return $this->x;
    }

    public function getY(): string {
        return $this->y;
    }

    public function getZ(): array {
        return $this->z;
    }

    public function getFoo(): ?Foo {
        return $this->foo;
    }

    public function setX(?int $x): static {
        $this->x = $x;
        return $this;
    }

    public function setY(?string $y): static {
        $this->y = $y;
        return $this;
    }

    public function setZ(array $z): static {
        $this->z = $z;
        return $this;
    }

    public function setFoo(?Foo $foo): static {
        $this->foo = $foo;
        return $this;
    }

    public function equals(mixed $foo): bool {
        
        if (!(is_object($foo) && $foo::class === Foo::class)) {
            return false;
        }

        if ($this->x !== $foo->x) {
            return false;
        }

        if (!hash_equals($this->y, $this->y)) {
            return false;
        }

        foreach ($this->z as $key => $val) {
            if (!array_key_exists($key, $foo->z)) {
                return false;
            }

            if ($val != $foo->z[$key]) {
                return false;
            }
        }

        if ($this->foo == null && $foo->foo === null) {
            return true;
        }

        return $this->foo !== null && $this->foo->equals($foo->foo);
    }

    #[Override]
    public function serialize(): string {
        return json_encode([
            'x' => $this->x
            , 'y' => $this->y
            , 'z' => $this->z
            , 'foo' => $this->foo?->serialize()
        ]);
    }

    #[Override]
    public function unserialize(string $serialized): void {
        $data = json_decode($serialized, true, 512, JSON_THROW_ON_ERROR);
        $this->x = $data['x'];
        $this->y = $data['y'];
        $this->z = $data['z'];
        if ($data['foo'] !== null) {
            $this->foo = new Foo();
            $this->foo->unserialize($data['foo']);
        }
    }
}
