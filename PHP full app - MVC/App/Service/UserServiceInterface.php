<?php

namespace App\Service;


use App\Data\UserDTO;

interface UserServiceInterface
{

    public function register(UserDTO $userDTO, $confirmPassword): bool;

    public function login(string $username, string $password): ?UserDTO;

    public function getCurrentUser(): ?UserDTO;

    public function editProfile(UserDTO $user): bool;

    /**
     * @return \Generator|UserDTO[]
     */
    public function viewAll(): \Generator;
}