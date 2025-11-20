<?php

namespace App\EventListener;

use App\Service\User\UserService;
use App\Service\ApiClientService;
use App\Helper\ErrorHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class TokenRefreshListener
{
    private const API_BASE_URL = 'https://candidate-testing.com/api/v2';

    public function __construct(
        private UserService $userService,
        private ApiClientService $apiClientService,
        private RouterInterface $router
    ) {}

    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();

        if (!$session->isStarted()) {
            $session->start();
        }

        $route = $request->attributes->get('_route');

        if (in_array($route, ['app_login', 'app_logout'])) {
            return;
        }

        /** @var \App\Dto\AuthResponseDto|null $userAuth */
        $userAuth = $this->userService->get('user');

        if (!$userAuth || !$userAuth->getUser() || !$userAuth->getTokenKey()) {
            ErrorHelper::addFlash($session, 'error', 'Please log in.');
            $event->setController(fn() => new RedirectResponse($this->router->generate('app_login')));
            return;
        }

        $expiresAt = $this->convertToDateTime($userAuth->getExpiresAt());

        if ($expiresAt !== null) {
            $minutesLeft = ($expiresAt->getTimestamp() - time()) / 60;

            if ($minutesLeft > 3) {
                return;
            }
        }

        try {
            $refreshToken = $userAuth->getRefreshTokenKey();
            $currentToken = $userAuth->getTokenKey();

            $response = $this->apiClientService->request(
                'GET',
                self::API_BASE_URL . "/token/refresh/$refreshToken",
                ['headers' => ['Accept' => 'application/json']]
            );

            $data = $response->toArray(false);

            $userAuth->setTokenKey($data['token_key'] ?? $currentToken);
            $userAuth->setExpiresAt($data['expires_at'] ?? $userAuth->getExpiresAt());
            $userAuth->setRefreshTokenKey($data['refresh_token_key'] ?? $userAuth->getRefreshTokenKey());
            $userAuth->setRefreshExpiresAt($data['refresh_expires_at'] ?? $userAuth->getRefreshExpiresAt());

            $this->userService->set('user', $userAuth);

        } catch (\Exception $e) {
            $this->userService->clear();
            ErrorHelper::addFlash($session, 'error', 'Session expired. Please log in again.');
            $event->setController(fn() => new RedirectResponse($this->router->generate('app_login')));
        }
    }

    private function convertToDateTime(?string $value): ?\DateTimeImmutable
    {
        if (!$value) {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
