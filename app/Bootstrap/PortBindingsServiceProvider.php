<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Application\Ports\Inbound\Authenticate;
use App\Application\Ports\Inbound\GetSales;
use App\Application\Ports\Inbound\GetSalesReport;
use App\Application\Ports\Inbound\ManageProducts;
use App\Application\Ports\Inbound\PlaceSale;
use App\Application\Ports\Outbound\CategoryRepository;
use App\Application\Ports\Outbound\Clock;
use App\Application\Ports\Outbound\FileStorage;
use App\Application\Ports\Outbound\PasswordHasher;
use App\Application\Ports\Outbound\ProductRepository;
use App\Application\Ports\Outbound\SaleRepository;
use App\Application\Ports\Outbound\SalesReportQuery;
use App\Application\Ports\Outbound\TokenGenerator;
use App\Application\Ports\Outbound\UnitOfWork;
use App\Application\Ports\Outbound\UserRepository;
use App\Application\UseCase\AuthenticationService;
use App\Application\UseCase\GetSalesService;
use App\Application\UseCase\SalesReportService;
use App\Application\UseCase\PlaceSaleService;
use App\Application\UseCase\ProductCatalogService;
use App\Domain\Model\User;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\Username;
use App\Infrastructure\Clock\SystemClock;
use App\Infrastructure\Persistence\LaravelUnitOfWork;
use App\Infrastructure\Persistence\Query\MySqlSalesReportQuery;
use App\Infrastructure\Persistence\Repository\EloquentCategoryRepository;
use App\Infrastructure\Persistence\Repository\EloquentProductRepository;
use App\Infrastructure\Persistence\Repository\EloquentSaleRepository;
use App\Infrastructure\Persistence\Repository\EloquentUserRepository;
use App\Infrastructure\Security\Argon2PasswordHasher;
use App\Infrastructure\Security\JwtTokenGenerator;
use App\Infrastructure\Storage\LocalFileStorage;
use Illuminate\Support\ServiceProvider;

final class PortBindingsServiceProvider extends ServiceProvider
{
    /**
     * Register outbound and inbound port bindings in the IoC container.
     */
    public function register(): void
    {
        // 10 Outbound Ports (Infrastructure Adapters)
        $this->app->bind(UnitOfWork::class, LaravelUnitOfWork::class);
        $this->app->bind(ProductRepository::class, EloquentProductRepository::class);
        $this->app->bind(SaleRepository::class, EloquentSaleRepository::class);
        $this->app->bind(CategoryRepository::class, EloquentCategoryRepository::class);
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(SalesReportQuery::class, MySqlSalesReportQuery::class);
        $this->app->bind(FileStorage::class, LocalFileStorage::class);
        $this->app->bind(PasswordHasher::class, Argon2PasswordHasher::class);
        $this->app->bind(TokenGenerator::class, JwtTokenGenerator::class);
        $this->app->bind(Clock::class, SystemClock::class);

        // 5 Inbound Ports (Application Use Cases)
        $this->app->bind(PlaceSale::class, PlaceSaleService::class);
        $this->app->bind(ManageProducts::class, ProductCatalogService::class);
        $this->app->bind(GetSales::class, GetSalesService::class);
        $this->app->bind(GetSalesReport::class, SalesReportService::class);
        $this->app->bind(Authenticate::class, AuthenticationService::class);
    }

    /**
     * Bootstrap services - provisions initial admin on startup per data-model.md §9.2
     */
    public function boot(): void
    {
        $this->bootstrapAdmin();
    }

    private function bootstrapAdmin(): void
    {
        try {
            $adminEmail = env('ADMIN_EMAIL');
            $adminPassword = env('ADMIN_PASSWORD');
            if (!$adminEmail || !$adminPassword) {
                return;
            }

            /** @var UserRepository $userRepository */
            $userRepository = $this->app->make(UserRepository::class);
            /** @var PasswordHasher $hasher */
            $hasher = $this->app->make(PasswordHasher::class);

            $username = new Username((string) $adminEmail);
            $existing = $userRepository->findByUsername($username);

            if ($existing === null) {
                $admin = new User(
                    id: UserId::generate(),
                    username: $username,
                    passwordHash: $hasher->hash((string) $adminPassword),
                    role: Role::admin()
                );
                $userRepository->save($admin);
            }
        } catch (\Throwable $e) {
            // Silently continue if database or tables do not exist yet
        }
    }
}

