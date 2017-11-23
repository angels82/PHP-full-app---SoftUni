<?php

namespace App\Service;


use App\Data\UserDTO;
use App\Repository\UserRepositoryInterface;

class UserService implements UserServiceInterface
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

// service layer
    public function register(UserDTO $userDTO,
                             $confirmPassword): bool
    {
        if ($userDTO->getPassword() != $confirmPassword) {
            return false;
        }

        if (null !== $this->userRepository->findOneByUsername($userDTO->getUsername())) {
            return false;
        }

        $plainPassword = $userDTO->getPassword();
        $passwordHash = password_hash($plainPassword, PASSWORD_BCRYPT);
        $userDTO->setPassword($passwordHash);

        return $this->userRepository->insert($userDTO);
    }

    public function login(string $username,
                          string $password): ?UserDTO
    {
        $user = $this->userRepository->findOneByUsername($username);
        if (null === $user) {
            return null;
        }

        $passwordHash = $user->getPassword();

        if (true === password_verify($password, $passwordHash)) {
            return $user;
        }

        return null;
    }

    public function getCurrentUser(): ?UserDTO
    {
        if (!isset($_SESSION['id'])) {
            return null;
        }

        return $this->userRepository->findOne($_SESSION['id']);
    }

    public function editProfile(UserDTO $user): bool
    {
        $existingUser = $this->userRepository->findOneByUsername($user->getUsername());
        if (null !== $existingUser) {
            return false;
        }

        $plainPassword = $user->getPassword();
        $passwordHash = password_hash($plainPassword, PASSWORD_BCRYPT);
        $user->setPassword($passwordHash);

        return $this->userRepository->update($_SESSION['id'], $user);
    }

    /**
     * @return \Generator|UserDTO[]
     */
    public function viewAll(): \Generator
    {
        return $this->userRepository->findAll();
    }
}