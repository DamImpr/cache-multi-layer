<?php



namespace App\Test\Service;

use PHPUnit\Framework\TestCase;

/**
 * Description of Foo
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class Foo extends TestCase {
    
    public function testAdd()
    {
        $this->assertEquals(5, 3+2);
    }
    
}
