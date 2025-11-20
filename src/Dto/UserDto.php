<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\SerializedName;

class UserDto
{
    #[SerializedName('id')]
    private ?int $id = null;

    #[SerializedName('first_name')]
    private ?string $firstName = null;

    #[SerializedName('last_name')]
    private ?string $lastName = null;

    #[SerializedName('email')]
    private ?string $email = null;

    public function getId(): ?int { return $this->id; }
    public function getFirstName(): ?string { return $this->firstName; }
    public function getLastName(): ?string { return $this->lastName; }
    public function getEmail(): ?string { return $this->email; }

    public function setId(?int $id): void { $this->id = $id; }
    public function setFirstName(?string $firstName): void { $this->firstName = $firstName; }
    public function setLastName(?string $lastName): void { $this->lastName = $lastName; }
    public function setEmail(?string $email): void { $this->email = $email; }
}
