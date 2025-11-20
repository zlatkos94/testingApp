<?php

namespace App\Service\User;

interface UserStorageInterface
{
    public function set(string $key, mixed $value): void;
    public function get(string $key, mixed $default = null): mixed;
    public function remove(string $key): void;
    public function clear(): void;
}
