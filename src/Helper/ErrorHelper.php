<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ErrorHelper
{
    public static function addFlash(SessionInterface $session, string $type, string $message): void
    {
        $session->getFlashBag()->add($type, $message);
    }
}