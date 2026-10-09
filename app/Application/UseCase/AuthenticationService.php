<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\AuthResultView;
use App\Application\DTO\LoginCommand;
use App\Application\DTO\RegisterUserCommand;
use App\Application\Ports\Inbound\Authenticate;
use App\Application\Ports\Outbound\PasswordHasher;
use App\Application\Ports\Outbound\TokenGenerator;
use App\Application\Ports\Outbound\UserRepository;
use App\Domain\Exception\BusinessRuleViolation;
use App\Domain\Exception\DuplicateUsernameException;
use App\Domain\Model\User;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\Username;
use Ramsey\Uuid\Uuid;

final class AuthenticationService implements Authenticate
{
    private UserRepository $userRepository;
    private PasswordHasher $passwordHasher;
    private TokenGenerator $tokenGenerator;

    public function __construct(
        UserRepository $userRepository,
        PasswordHasher $passwordHasher,
        TokenGenerator $tokenGenerator
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->tokenGenerator = $tokenGenerator;
    }

    public function login(LoginCommand $command): AuthResultView
    {
        $username = trim($command->getUsername());
        if ($username === '') {
            throw new BusinessRuleViolation('Usuario o contraseña incorrectos.');
        }

        $user = $this->userRepository->findByUsername($username);
        if ($user === null) {
            throw new BusinessRuleViolation('Usuario o contraseña incorrectos.');
        }

        if (!$this->passwordHasher->verify($command->getPassword(), $user->getPasswordHash())) {
            throw new BusinessRuleViolation('Usuario o contraseña incorrectos.');
        }

        return $this->tokenGenerator->generate($user);
    }

    public function registerSeller(RegisterUserCommand $command): AuthResultView
    {
        $usernameVO = new Username($command->getUsername());
        $existing = $this->userRepository->findByUsername($usernameVO->getValue());
        if ($existing !== null) {
            throw new DuplicateUsernameException("El nombre de usuario '{$usernameVO->getValue()}' ya se encuentra registrado.");
        }

        if (strlen($command->getPassword()) < 8) {
            throw new BusinessRuleViolation('La contraseña debe tener al menos 8 caracteres.');
        }

        $id = Uuid::uuid4()->toString();
        $hash = $this->passwordHasher->hash($command->getPassword());
        $role = new Role(Role::SELLER);

        $user = new User($id, $usernameVO, $hash, $role);
        $this->userRepository->save($user);

        return $this->tokenGenerator->generate($user);
    }
}

