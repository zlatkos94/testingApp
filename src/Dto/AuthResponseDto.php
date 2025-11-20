<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\SerializedName;

class AuthResponseDto
{
    #[SerializedName('token_key')]
    private ?string $tokenKey = null;

    #[SerializedName('refresh_token_key')]
    private ?string $refreshTokenKey = null;

    #[SerializedName('expires_at')]
    private ?string $expiresAt = null;

    #[SerializedName('refresh_expires_at')]
    private ?string $refreshExpiresAt = null;

    private ?UserDto $user = null;

    public function getTokenKey(): ?string { return $this->tokenKey; }
    public function getRefreshTokenKey(): ?string { return $this->refreshTokenKey; }
    public function getExpiresAt(): ?string { return $this->expiresAt; }
    public function getRefreshExpiresAt(): ?string { return $this->refreshExpiresAt; }
    public function getUser(): ?UserDto { return $this->user; }

    public function setTokenKey(?string $tokenKey): void { $this->tokenKey = $tokenKey; }
    public function setRefreshTokenKey(?string $refreshTokenKey): void { $this->refreshTokenKey = $refreshTokenKey; }
    public function setExpiresAt(?string $expiresAt): void { $this->expiresAt = $expiresAt; }
    public function setRefreshExpiresAt(?string $refreshExpiresAt): void { $this->refreshExpiresAt = $refreshExpiresAt; }
    public function setUser(?UserDto $user): void { $this->user = $user; }
}