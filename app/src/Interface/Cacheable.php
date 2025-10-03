<?php

namespace CacheMultiLayer\Interface;

/**
 *
 * Interface that establishes that an entity can be stored in the various levels of the cache system.
 * The interface requires the implementation of two methods, the first to obtain the properties with their values
 * in a string, and the second to repopulate its properties using the input string
 * When implementing the interface methods, be careful to always serialise and deserialise
 * the same properties, otherwise the entity will have missing values when read from the cache
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
interface Cacheable
{
    public function serialize(): string;

    public function unserialize(string $serialized): void;
}
