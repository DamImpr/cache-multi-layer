<?php

namespace CacheMultiLayer\Interface;

/**
 * 
 * Interfaccia che stabilisce che un'entità è memorizzabile nei vari livelli del  sistema di cache.
 * L'interfaccia impone di implementare due metodi che servono, il primo per avere le proprietà con i relativi valori
 * in una stringa, e il secondo per ripopolare le proprie proprietà tramite la stringa in ingresso
 * Nell'implementazione dei metodi dell'interfaccia, prestare attenzione a serializzare e deserializzare 
 * sempre le stesse proprietà, pena ritrovarsi l'entità con valori mancanti quando letta dalla cache
 * 
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
interface Cacheable
{

    public function serialize(): string;

    public function unserialize(string $serialized): void;
}
