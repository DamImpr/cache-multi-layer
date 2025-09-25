<?php

namespace CacheMultiLayer\Tests\Entity;

use CacheMultiLayer\Interface\Cacheable;
use Override;

/**
 * 
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class Foo implements Cacheable{

    private int $x;
    private string $y;
    private array $z;
    private ?Foo $foo;

    public function __construct(int $x, string $y, array $z, ?Foo $foo) {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->foo = $foo;
    }

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

    public function setX(int $x): void {
        $this->x = $x;
    }

    public function setY(string $y): void {
        $this->y = $y;
    }

    public function setZ(array $z): void {
        $this->z = $z;
    }

    public function setFoo(?Foo $foo): void {
        $this->foo = $foo;
    }

    #[Override]
    public function serialize(): string {
        return json_encode([
            'x' => $this->x 
            ,'y' => $this->y
            ,'z' => $this->z
            ,'foo' => $this->foo?->serialize()
        ]);
    }

    #[Override]
    public function unserialize(string $serialized): void {
        $data = json_decode($serialized,true);
        $this->x = $data['x'];
        $this->y = $data['y'];
        $this->z = $data['z'];
        $this->foo = $data['foo']?->unserialize();
    }
    
      
}
