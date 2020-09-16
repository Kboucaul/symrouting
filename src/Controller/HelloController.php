<?php

namespace App\Controller;

class HelloController
{
    /**
     * Fonction chargée de dire bonjour a
     * l'utilisateur.
     */
    public function sayHello(array $currentRoute)
    {
        require __DIR__.'/../../pages/hello.html.php';
    }
}