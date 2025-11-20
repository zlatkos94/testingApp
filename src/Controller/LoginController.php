<?php

namespace App\Controller;

use App\Dto\AuthResponseDto;
use App\Helper\ErrorHelper;
use App\Service\ApiClientService;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class LoginController extends AbstractController
{
    private UserService $userService;
    private ApiClientService $apiClientService;
    private string $apiTokenUrl;
    private SerializerInterface $serializer;

    public function __construct(
        UserService $userService,
        ApiClientService $apiClientService,
        SerializerInterface $serializer,
        string $apiTokenUrl
    ) {
        $this->userService = $userService;
        $this->apiClientService = $apiClientService;
        $this->apiTokenUrl = $apiTokenUrl;
        $this->serializer = $serializer;
    }

    #[Route('/login', name: 'app_login')]
    public function login(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('login.html.twig');
        }

        $email = $request->request->get('username');
        $password = $request->request->get('password');

        try {
            $response = $this->apiClientService->request(
                Request::METHOD_POST,
                $this->apiTokenUrl,
                [
                    'headers' => [
                        'accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'email' => $email,
                        'password' => $password,
                    ],
                ]
            );
            $json = $response->getContent();
            /** @var AuthResponseDto $authResponse */
            $authResponse = $this->serializer->deserialize($json, AuthResponseDto::class, 'json');
            $this->userService->set('user', $authResponse);
            return $this->redirectToRoute('authors');

        } catch (ClientExceptionInterface $e) {
            ErrorHelper::addFlash($request->getSession(), 'error', 'Invalid username or password.');
            return $this->render('login.html.twig', ['username' => $email]);
        } catch (\Exception $e) {
            ErrorHelper::addFlash($request->getSession(), 'error', 'API error: ' . $e->getMessage());
            return $this->render('login.html.twig', ['username' => $email]);
        }
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): Response
    {
        $this->userService->clear();

        return $this->redirectToRoute('app_login');
    }
}
