<?php

namespace App\Service\User;

class UserService
{
    public function __construct(private UserStorageInterface $storage)
    {
    }

    public function set(string $key, mixed $value): void
    {
        $this->storage->set($key, $value);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->storage->get($key, $default);
    }

    public function remove(string $key): void
    {
        $this->storage->remove($key);
    }

    public function clear(): void
    {
        $this->storage->clear();
    }
}
