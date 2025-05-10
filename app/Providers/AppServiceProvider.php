<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Auth\AuthServiceInterface;
use App\Services\Auth\AuthService;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\Services\TypeServices\TypeServiceRepositoryInterface;
use App\Repositories\Services\TypeServices\TypeServiceRepository;
use App\Services\Services\TypeServices\TypeServiceServiceInterface;
use App\Services\Services\TypeServices\TypeServiceService;
use App\Repositories\Services\ServiceRepositoryInterface;
use App\Repositories\Services\ServiceRepository;
use App\Services\Services\ServiceServiceInterface;
use App\Services\Services\ServiceService;
use App\Repositories\TimeBlocks\TimeBlockRepositoryInterface;
use App\Repositories\TimeBlocks\TimeBlockRepository;
use App\Services\TimeBlocks\TimeBlockServiceInterface;
use App\Services\TimeBlocks\TimeBlockService;
use App\Services\Product\Inventory\InventoryServiceInterface;
use App\Services\Product\Inventory\InventoryService;
use App\Repositories\Product\Inventory\InventoryRepositoryInterface;
use App\Repositories\Product\Inventory\InventoryRepository;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\Product\ProductRepository;
use App\Services\Product\ProductServiceInterface;
use App\Services\Product\ProductService;
use App\Repositories\Evaluation\EvaluationRepositoryInterface;
use App\Repositories\Evaluation\EvaluationRepository;
use App\Services\Evaluation\EvaluationServiceInterface;
use App\Services\Evaluation\EvaluationService;
use App\Repositories\Notification\NotificationRepositoryInterface;
use App\Repositories\Notification\NotificationRepository;
use App\Services\Notification\NotificationServiceInterface;
use App\Services\Notification\NotificationService;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Colors\Color;
use Filament\Facades\Filament;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        $this->app->singleton(TypeServiceServiceInterface::class, TypeServiceService::class);
        $this->app->singleton(TypeServiceRepositoryInterface::class, TypeServiceRepository::class);

        $this->app->singleton(ServiceServiceInterface::class, ServiceService::class);
        $this->app->singleton(ServiceRepositoryInterface::class, ServiceRepository::class);

        $this->app->bind(TimeBlockRepositoryInterface::class, TimeBlockRepository::class);
        $this->app->bind(TimeBlockServiceInterface::class, TimeBlockService::class);

        $this->app->bind(InventoryServiceInterface::class, InventoryService::class);
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);

        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);

        $this->app->bind(EvaluationRepositoryInterface::class, EvaluationRepository::class);
        $this->app->bind(EvaluationServiceInterface::class, EvaluationService::class);

        $this->app->bind(NotificationRepositoryInterface::class, NotificationRepository::class);
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);
    }

    public function boot(): void
    {
        Filament::serving(function () {
            Filament::registerNavigationGroups([
                NavigationGroup::make()
                    ->label('Administración de Usuarios')
                    ->icon('heroicon-o-users')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Gestión de Servicios')
                    ->icon('heroicon-o-rectangle-stack'),
                NavigationGroup::make()
                    ->label('Programación')
                    ->icon('heroicon-o-calendar'),
                NavigationGroup::make()
                    ->label('Inventario')
                    ->icon('heroicon-o-cube'),
                NavigationGroup::make()
                    ->label('Sistema')
                    ->icon('heroicon-o-cog'),

            ]);
        });
    }
}
