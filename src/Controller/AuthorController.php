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

class AuthorController extends AbstractController
{
    private const API_BASE_URL = 'https://candidate-testing.com/api/v2';

    private ApiClientService $apiClientService;
    private UserService $userService;

    public function __construct(ApiClientService $apiClientService, UserService $userService)
    {
        $this->apiClientService = $apiClientService;
        $this->userService = $userService;
    }

    #[Route('/authors', name: 'authors')]
    public function authors(Request $request): Response
    {
        /** @var AuthResponseDto $userAuth */
        $userAuth = $this->userService->get('user');

        if (!$userAuth->getUser() && $userAuth->getTokenKey() === null) {
            return $this->redirectToRoute('app_login');
        }

        $authors = [];
        try {
            $response = $this->apiClientService->request(
                Request::METHOD_GET,
                self::API_BASE_URL . '/authors?orderBy=id&direction=ASC&limit=12&page=1',
                ['headers' => ['Authorization' => $userAuth->getTokenKey()]]
            );

            $data = $response->toArray(false);
            $authors = $data['items'] ?? [];

        } catch (\Exception $e) {
            ErrorHelper::addFlash($request->getSession(), 'error', 'Failed to fetch authors: ' . $e->getMessage());
        }

        return $this->render('authors.html.twig', ['authors' => $authors]);
    }

    #[Route('/authors/{id}', name: 'author_detail')]
    public function detail(Request $request, int $id): Response
    {
        /** @var AuthResponseDto $userAuth */
        $userAuth = $this->userService->get('user');

        if (!$userAuth->getUser() && $userAuth->getTokenKey() === null) {
            return $this->redirectToRoute('app_login');
        }

        $author = null;
        try {
            $response = $this->apiClientService->request(
                Request::METHOD_GET,
                self::API_BASE_URL . "/authors/{$id}",
                ['headers' => ['Authorization' => $userAuth->getTokenKey()]]
            );
            $author = $response->toArray(false);
        } catch (\Exception $e) {
            ErrorHelper::addFlash($request->getSession(), 'error', 'Failed to fetch author: ' . $e->getMessage());
        }

        return $this->render('author_detail.html.twig', ['author' => $author]);
    }

    #[Route('/authors/{id}/delete', name: 'author_delete', methods: [Request::METHOD_POST])]
    public function delete(int $id, Request $request): Response
    {
        /** @var AuthResponseDto $userAuth */
        $userAuth = $this->userService->get('user');

        if (!$userAuth->getUser() && $userAuth->getTokenKey() === null) {
            ErrorHelper::addFlash($request->getSession(), 'error', 'User not logged in.');
            return $this->redirectToRoute('authors');
        }

        try {
            $response = $this->apiClientService->request(
                Request::METHOD_GET,
                self::API_BASE_URL . "/authors/{$id}",
                ['headers' => ['Authorization' => $userAuth->getTokenKey()]]
            );
            $author = $response->toArray(false);

            if (!empty($author['books'])) {
                ErrorHelper::addFlash($request->getSession(), 'error', 'Cannot delete author with books.');
                return $this->redirectToRoute('author_detail', ['id' => $id]);
            }

            $this->apiClientService->request(
                Request::METHOD_DELETE,
                self::API_BASE_URL . "/authors/{$id}",
                ['headers' => ['Authorization' => $userAuth->getTokenKey()]]
            );

            ErrorHelper::addFlash($request->getSession(), 'success', 'Author deleted successfully.');

        } catch (\Exception $e) {
            ErrorHelper::addFlash($request->getSession(), 'error', 'Failed to delete author: ' . $e->getMessage());
        }

        return $this->redirectToRoute('authors');
    }

    #[Route('/authors/{authorId}/books/{bookId}/delete', name: 'book_delete', methods: [Request::METHOD_POST])]
    public function deleteBook(int $authorId, int $bookId, Request $request): Response
    {
        /** @var AuthResponseDto $userAuth */
        $userAuth = $this->userService->get('user');
        if (!$userAuth->getUser() && $userAuth->getTokenKey() === null) {
            ErrorHelper::addFlash($request->getSession(), 'error', 'User not logged in.');
            return $this->redirectToRoute('authors');
        }

        try {
            $this->apiClientService->request(
                Request::METHOD_DELETE,
                self::API_BASE_URL . "/books/{$bookId}",
                ['headers' => ['Authorization' => $userAuth->getTokenKey()]]
            );

            ErrorHelper::addFlash($request->getSession(), 'success', 'Book deleted successfully.');
        } catch (\Exception $e) {
            ErrorHelper::addFlash($request->getSession(), 'error', 'Failed to delete book: ' . $e->getMessage());
        }

        return $this->redirectToRoute('author_detail', ['id' => $authorId]);
    }
}
