<?php

namespace App\Command;

use App\Service\ApiClientService;
use App\Dto\AuthResponseDto;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'app:author:create',
    description: 'Login and create a new author via API'
)]
class CreateAuthorCommand extends Command
{
    private const API_BASE_URL = 'https://candidate-testing.com/api/v2';

    public function __construct(
        private ApiClientService $apiClientService,
        private SerializerInterface $serializer,
        private string $apiTokenUrl
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $io->ask('Email');
        $password = $io->askHidden('Password');

        if (!$email || !$password) {
            $io->error('Email and password are required.');
            return Command::FAILURE;
        }

        try {
            $response = $this->apiClientService->request(
                'POST',
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

            $token = $authResponse->getTokenKey();
            if (!$token) {
                $io->error('Login failed: no token received.');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error('Login failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // --- ASK FOR AUTHOR DATA ---
        $firstName = $io->ask('First name');
        $lastName = $io->ask('Last name');
        $birthday = $io->ask('Birthday (YYYY-MM-DD)');
        $biography = $io->ask('Biography');
        $gender = $io->choice('Gender', ['male', 'female', 'other'], 'male');
        $placeOfBirth = $io->ask('Place of birth');

        if (!$firstName || !$lastName || !$birthday || !$biography || !$gender || !$placeOfBirth) {
            $io->error('All fields are required.');
            return Command::FAILURE;
        }

        try {
            $response = $this->apiClientService->request(
                'POST',
                self::API_BASE_URL . '/authors',
                [
                    'headers' => [
                        'Authorization' => $token,
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'birthday' => $birthday,
                        'biography' => $biography,
                        'gender' => $gender,
                        'place_of_birth' => $placeOfBirth,
                    ],
                ]
            );

            $data = $response->toArray(false);
            $io->success("Author created! ID: {$data['id']}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Failed to create author: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
