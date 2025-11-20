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

class BookController extends AbstractController
{
    private const API_BASE_URL = 'https://candidate-testing.com/api/v2';

    private ApiClientService $apiClientService;
    private UserService $userService;

    public function __construct(ApiClientService $apiClientService, UserService $userService)
    {
        $this->apiClientService = $apiClientService;
        $this->userService = $userService;
    }

    #[Route('/books/new', name: 'book_new', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function new(Request $request): Response
    {
        /** @var AuthResponseDto $userAuth */
        $userAuth = $this->userService->get('user');

        if (!$userAuth->getUser() && $userAuth->getTokenKey() === null) {
            ErrorHelper::addFlash($request->getSession(), 'error', 'No valid API token found. Please log in.');
            return $this->redirectToRoute('app_login');
        }

        $authors = [];
        try {
            $response = $this->apiClientService->request(
                Request::METHOD_GET,
                self::API_BASE_URL . '/authors',
                ['headers' => ['Authorization' => $userAuth->getTokenKey()]]
            );
            $data = $response->toArray(false);
            $authors = $data['items'] ?? [];
        } catch (\Exception $e) {
            ErrorHelper::addFlash($request->getSession(), 'error', 'Failed to fetch authors: ' . $e->getMessage());
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            $authorId = (int)$request->request->get('author_id');

            $payload = [
                'author' => ['id' => $authorId],
                'title' => $request->request->get('title'),
                'release_date' => $request->request->get('release_date'),
                'description' => $request->request->get('description'),
                'isbn' => $request->request->get('isbn'),
                'format' => $request->request->get('format'),
                'number_of_pages' => (int)$request->request->get('number_of_pages'),
            ];

            try {
                $this->apiClientService->request(
                    Request::METHOD_POST,
                    self::API_BASE_URL . '/books',
                    [
                        'headers' => [
                            'Authorization' => $userAuth->getTokenKey(),
                            'Content-Type' => 'application/json',
                        ],
                        'json' => $payload,
                    ]
                );

                ErrorHelper::addFlash($request->getSession(), 'success', 'Book added successfully!');
            } catch (\Exception $e) {
                ErrorHelper::addFlash($request->getSession(), 'error', 'Failed to add book: ' . $e->getMessage());
            }

            return $this->redirectToRoute('author_detail', ['id' => $authorId]);
        }

        return $this->render('book.html.twig', ['authors' => $authors]);
    }
}
