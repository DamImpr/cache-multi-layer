<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace App\Exception;

/**
 * Description of CacheMissingConfigurationException
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class CacheMissingConfigurationException  extends Exception{
    
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = NULL) {
        parent::__construct($message, $code, $previous);
    }

    
}