<?php

namespace App\Service\User;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionUserStorage implements UserStorageInterface
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    private function getSession(): ?SessionInterface
    {
        return $this->requestStack->getSession();
    }

    public function set(string $key, mixed $value): void
    {
        $this->getSession()?->set($key, $value);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getSession()?->get($key, $default);
    }

    public function remove(string $key): void
    {
        $this->getSession()?->remove($key);
    }

    public function clear(): void
    {
        $this->getSession()?->clear();
    }
}
