<?php

namespace App\Test;

use PHPUnit\Framework\TestCase;
/**
 * Description of FooTest
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class BarTest  extends TestCase {
    
    public function testAdd()
    {
        $this->assertEquals(7, 3+2);
    }
}