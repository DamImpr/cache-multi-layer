<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace App\Test;

use PHPUnit\Framework\TestCase;
/**
 * Description of FooTest
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class FooTest  extends TestCase {
    
    public function testAdd()
    {
        $this->assertEquals(5, 3+2);
    }
}