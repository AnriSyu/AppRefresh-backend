<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use App\Models\User;
use App\Models\TypeService;
use App\Models\ServicesAvailability;
use App\Models\TimeBlock;
use App\Models\ServiceHistory;
use App\Models\Notifications;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Get;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;
    protected static ?string $navigationGroup = 'Gestión de Servicios';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nombre del servicio')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn (string $operation): bool => $operation === 'edit'),

                Textarea::make('description')
                    ->label('Descripción')
                    ->required(),

                TextInput::make('price')
                    ->label('Precio')
                    ->required()
                    ->numeric()
                    ->prefix('$'),

                TextInput::make('duration')
                    ->label('Duración')
                    ->required()
                    ->helperText('Ejemplo: 30 minutos, 2 horas, etc.'),

                Select::make('type_service_id')
                    ->label('Tipo de servicio')
                    ->options(TypeService::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                Select::make('client_id')
                    ->label('Cliente')
                    ->options(User::whereHas('roles', function ($query) {
                        $query->where('name', 'Cliente');
                    })->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->disabled(fn (string $operation): bool => $operation === 'edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->label('Nombre')
                ->formatStateUsing(function ($state) {
                    if (strlen($state) > 15) {
                        return substr($state, 0, 15) . '...';
                    }
                    return $state;
                })
                ->extraAttributes(fn ($state) => [
                    'title' => $state,
                ])
                ->searchable(),

                Tables\Columns\TextColumn::make('typeService.name')
                    ->label('Tipo de servicio')
                    ->formatStateUsing(function ($state) {
                        if (strlen($state) > 25) {
                            return substr($state, 0, 25) . '...';
                        }
                        return $state;
                    })
                    ->extraAttributes(fn ($state) => [
                        'title' => $state,
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                      ->formatStateUsing(function ($state) {
                    if (strlen($state) > 15) {
                        return substr($state, 0, 15) . '...';
                    }
                    return $state;
                })
                ->extraAttributes(fn ($state) => [
                    'title' => $state,
                ])
                    ->searchable(),

                Tables\Columns\TextColumn::make('technical.name')
                    ->label('Técnico')
                    ->formatStateUsing(function ($state) {
                        if (strlen($state) > 15) {
                            return substr($state, 0, 15) . '...';
                        }
                        return $state;
                    })
                    ->extraAttributes(fn ($state) => [
                        'title' => $state,
                    ])
                    ->searchable()
                    ->placeholder('Sin asignar'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('COP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration')
                    ->label('Duración')
                    ->formatStateUsing(function ($state) {
                        if (strlen($state) > 15) {
                            return substr($state, 0, 15) . '...';
                        }
                        return $state;
                    })
                    ->extraAttributes(fn ($state) => [
                        'title' => $state,
                    ]),

                Tables\Columns\TextColumn::make('history')
                    ->label('Estado actual')
                    ->getStateUsing(function (Service $record) {
                        $latestHistory = $record->history()->latest()->first();
                        return $latestHistory ? $latestHistory->statusName : 'Desconocido';
                    })
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'Pendiente' => 'warning',
                            'Activo' => 'success',
                            'En progreso' => 'info',
                            'Cancelado' => 'danger',
                            'Finalizado' => 'success',
                            default => 'gray',
                        };
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                self::getTypeServiceFilter(),
                self::getStatusFilter(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                self::getAssignTechnicalAction(),
                self::getChangeStatusAction(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\HistoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
            'view' => Pages\ViewService::route('/{record}'),
        ];
    }

    private static function getTypeServiceFilter()
    {
        return Tables\Filters\SelectFilter::make('type_service')
            ->relationship('typeService', 'name')
            ->label('Tipo de servicio');
    }

    private static function getStatusFilter()
    {
        return Tables\Filters\SelectFilter::make('status')
            ->label('Estado')
            ->options([
                'P' => 'Pendiente',
                'A' => 'Activo',
                'F' => 'Finalizado',
                'C' => 'Cancelado',
                'E' => 'En progreso',
            ])
            ->query(function (Builder $query, array $data) {
                if (isset($data['value'])) {
                    $query->whereHas('history', function ($query) use ($data) {
                        $query->where('status', $data['value'])
                            ->whereIn('id', function ($subquery) {
                                $subquery->selectRaw('MAX(id)')
                                    ->from('services_history')
                                    ->groupBy('service_id');
                            });
                    });
                }
            });
    }

    private static function getAssignTechnicalAction()
    {
        return Tables\Actions\Action::make('assignTechnical')
            ->label(function (Service $record) {
                return $record->technical_id ? 'Reasignar técnico' : 'Asignar técnico';
            })
            ->icon('heroicon-o-user')
            ->form(function (Service $record) {
                $fields = [];

                if (!$record->technical_id) {
                    $fields[] = TextInput::make('price')
                        ->label('Precio')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->default($record->price);

                    $fields[] = TextInput::make('duration')
                        ->label('Duración')
                        ->required()
                        ->helperText('Ejemplo: 30 minutos, 2 horas, etc.')
                        ->default($record->duration);
                }

                $fields[] = Select::make('technical_id')
                    ->label('Técnico')
                    ->options(User::whereHas('roles', function ($query) {
                        $query->where('name', 'Técnico');
                    })->pluck('name', 'id'))
                    ->required()
                    ->searchable();

                return $fields;
            })
            ->action(function (array $data, Service $record) {
                $technicalId = $data['technical_id'];
                $oldTechnicalId = $record->technical_id;
                $isReassignment = $oldTechnicalId !== null;
                if (!$isReassignment) {
                    $record->update([
                        'price' => $data['price'],
                        'duration' => $data['duration'],
                    ]);
                }

                $serviceAvailability = ServicesAvailability::where('services_id', $record->id)->first();

                if (!$serviceAvailability || empty($serviceAvailability->time_block_ids)) {
                    FilamentNotification::make()
                        ->warning()
                        ->title('Información incompleta')
                        ->body('Este servicio no tiene horarios asignados.')
                        ->send();
                    return;
                }

                $serviceTimeBlocks = TimeBlock::whereIn('id', $serviceAvailability->time_block_ids)
                    ->get()
                    ->map(function ($timeBlock) {
                        return [
                            'day_of_week' => $timeBlock->day_of_week,
                            'hours' => $timeBlock->hours,
                        ];
                    });

                $technicianServices = Service::where('technical_id', $technicalId)
                    ->where('id', '!=', $record->id)
                    ->get();

                $hasConflict = false;

                foreach ($technicianServices as $techService) {
                    $techAvailability = ServicesAvailability::where('services_id', $techService->id)->first();

                    if (!$techAvailability || empty($techAvailability->time_block_ids)) {
                        continue;
                    }

                    $techTimeBlocks = TimeBlock::whereIn('id', $techAvailability->time_block_ids)
                        ->get()
                        ->map(function ($timeBlock) {
                            return [
                                'day_of_week' => $timeBlock->day_of_week,
                                'hours' => $timeBlock->hours,
                            ];
                        });

                    foreach ($serviceTimeBlocks as $serviceBlock) {
                        foreach ($techTimeBlocks as $techBlock) {
                            if ($serviceBlock['day_of_week'] === $techBlock['day_of_week']) {
                                list($serviceStart, $serviceEnd) = explode('-', $serviceBlock['hours']);
                                list($techStart, $techEnd) = explode('-', $techBlock['hours']);

                                $serviceStartMin = self::timeToMinutes($serviceStart);
                                $serviceEndMin = self::timeToMinutes($serviceEnd);
                                $techStartMin = self::timeToMinutes($techStart);
                                $techEndMin = self::timeToMinutes($techEnd);

                                if (
                                    ($serviceStartMin >= $techStartMin && $serviceStartMin < $techEndMin) ||
                                    ($serviceEndMin > $techStartMin && $serviceEndMin <= $techEndMin) ||
                                    ($serviceStartMin <= $techStartMin && $serviceEndMin >= $techEndMin)
                                ) {
                                    $hasConflict = true;
                                    break 3;
                                }
                            }
                        }
                    }
                }

                if ($hasConflict) {
                    FilamentNotification::make()
                        ->danger()
                        ->title('Asignación fallida')
                        ->body('El técnico ya tiene un servicio asignado en un horario que se solapa con este servicio.')
                        ->send();
                    return;
                }

                $record->update([
                    'technical_id' => $technicalId
                ]);

                ServiceHistory::create([
                    'service_id' => $record->id,
                    'status' => 'A',
                    'observations' => $isReassignment
                        ? 'Técnico reasignado al servicio'
                        : 'Técnico asignado al servicio',
                    'start_time' => now(),
                    'end_time' => null,
                ]);

                if ($isReassignment) {
                    self::sendTechnicianReassignmentNotification($record, $oldTechnicalId);
                } else {
                    self::sendServiceNotifications($record);
                }

                FilamentNotification::make()
                    ->success()
                    ->title($isReassignment ? 'Técnico reasignado' : 'Técnico asignado')
                    ->body($isReassignment
                        ? 'Se ha reasignado el técnico al servicio correctamente.'
                        : 'Se ha asignado el técnico al servicio correctamente.')
                    ->send();
            })
            ->visible(function (Service $record) {
                $latestHistory = $record->history()->latest()->first();
                return ($latestHistory && $latestHistory->status === 'P') || $record->technical_id !== null;
            });
    }

    private static function getChangeStatusAction()
    {
        return Tables\Actions\Action::make('changeStatus')
            ->label('Cambiar estado')
            ->icon('heroicon-o-arrow-path')
            ->form([
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'A' => 'Activo',
                        'C' => 'Cancelado',
                        'E' => 'En progreso',
                        'P' => 'Pendiente',
                        'F' => 'Finalizado',
                    ])
                    ->required(),

                Textarea::make('observations')
                    ->label('Observaciones')
                    ->required(),
            ])
            ->action(function (array $data, Service $record) {
                ServiceHistory::create([
                    'service_id' => $record->id,
                    'status' => $data['status'],
                    'observations' => $data['observations'],
                    'start_time' => now(),
                    'end_time' => in_array($data['status'], ['C', 'F']) ? now() : null,
                ]);

                if (in_array($data['status'], ['C', 'F'])) {
                    // Eliminamos la disponibilidad en lugar de las availabilities individuales
                    ServicesAvailability::where('services_id', $record->id)->delete();

                    $record->update(['technical_id' => null]);
                }

                FilamentNotification::make()
                    ->success()
                    ->title('Estado actualizado')
                    ->body('Se ha actualizado el estado del servicio correctamente.')
                    ->send();
            });
    }

    public static function sendServiceNotifications(Service $service)
    {
        $service = Service::with(['technical', 'client'])->find($service->id);

        $serviceName = $service->name ?? 'Sin nombre';
        $message = "El servicio '{$serviceName}' ha sido ";

        if ($service->technical_id && $service->technical) {
            $message .= "asignado al técnico {$service->technical->name}.";
        } else {
            $message .= "creado y está pendiente de asignación.";
        }

        $notification = Notifications::create([
            'title' => 'Nuevo servicio asignado',
            'message' => $message,
            'type' => 'info',
        ]);

        $userIds = [];

        if ($service->client_id) {
            $userIds[] = $service->client_id;
        }

        if ($service->technical_id) {
            $userIds[] = $service->technical_id;
        }

        if (!empty($userIds)) {
            $notification->users()->attach($userIds, ['is_read' => false]);
        }
    }

    public static function sendTechnicianReassignmentNotification(Service $service, $oldTechnicianId)
    {
        $service = Service::with(['technical', 'client'])->find($service->id);

        if (!$service->technical_id || !$service->technical) {
            return;
        }

        $serviceName = $service->name ?? 'Sin nombre';
        $message = "El servicio '{$serviceName}' ha sido reasignado al técnico {$service->technical->name}.";

        $notification = Notifications::create([
            'title' => 'Servicio reasignado',
            'message' => $message,
            'type' => 'info',
        ]);

        $userIds = [];

        if ($service->client_id) {
            $userIds[] = $service->client_id;
        }

        if ($service->technical_id) {
            $userIds[] = $service->technical_id;
        }

        if ($oldTechnicianId) {
            $userIds[] = $oldTechnicianId;
        }

        $userIds = array_unique($userIds);

        if (!empty($userIds)) {
            $notification->users()->attach($userIds, ['is_read' => false]);
        }
    }

    private static function timeToMinutes($time)
    {
        $parts = explode(':', $time);
        return (int)$parts[0] * 60 + (isset($parts[1]) ? (int)$parts[1] : 0);
    }
}
