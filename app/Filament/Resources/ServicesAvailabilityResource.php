<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ServicesAvailabilityResource\Pages;
use App\Models\ServicesAvailability;
use App\Models\TimeBlock;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class ServicesAvailabilityResource extends Resource
{
    protected static ?string $model = ServicesAvailability::class;
    protected static ?string $navigationGroup = 'Programación';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Disponibilidad del Servicio';
    protected static ?string $pluralModelLabel = 'Disponibilidades de Servicios';


    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('services_id')
                    ->label('Servicio')
                    ->options(function () {
                        return Service::all()->mapWithKeys(function ($service) {
                            $displayName = strlen($service->name) > 40 ? substr($service->name, 0, 37) . '...' : $service->name;
                            return [$service->id => $displayName];
                        });
                    })
                    ->required()
                    ->searchable(),

                // Añadir un campo de selección para el día de la semana primero
                Select::make('selected_day')
                    ->label('Día de la semana')
                    ->options([
                        'monday' => 'Lunes',
                        'tuesday' => 'Martes',
                        'wednesday' => 'Miércoles',
                        'thursday' => 'Jueves',
                        'friday' => 'Viernes',
                    ])
                    ->required()
                    ->reactive() // Actualiza los bloques de tiempo cuando se selecciona un día
                    ->afterStateUpdated(fn (callable $set) => $set('time_block_ids', [])), // Resetea la selección de bloques cuando cambia el día

                // Ahora bloques de tiempo filtrados por el día seleccionado
                Select::make('time_block_ids')
                    ->label('Bloques de Tiempo')
                    ->options(function (callable $get) {
                        $selectedDay = $get('selected_day');

                        if (!$selectedDay) {
                            return [];
                        }

                        return TimeBlock::where('day_of_week', $selectedDay)
                            ->get()
                            ->mapWithKeys(function ($timeBlock) {
                                $formattedHour = match ($timeBlock->hours) {
                                    '08:00-10:00' => '8:00 AM - 10:00 AM',
                                    '10:00-12:00' => '10:00 AM - 12:00 AM',
                                    '12:00-14:00' => '12:00 AM - 14:00 AM',
                                    '14:00-16:00' => '14:00 AM - 16:00 PM',
                                    '14:00-18:00' => '14:00 PM - 18:00 PM',
                                    default => $timeBlock->hours,
                                };

                                return [$timeBlock->id => $formattedHour];
                            });
                    })
                    ->multiple()
                    ->required()
                    ->searchable()
                    ->dehydrated(true) // Aseguramos que se guarde en la base de datos
                    ->rules([
                        function (Forms\Get $get, $record) {
                            return function ($attribute, $value, $fail) use ($get, $record) {
                                if (empty($value)) {
                                    return;
                                }

                                $serviceId = $get('services_id');
                                $selectedDay = $get('selected_day');

                                if (!$serviceId || !$selectedDay) {
                                    return;
                                }

                                // Obtenemos los bloques de tiempo seleccionados
                                $selectedTimeBlocks = TimeBlock::whereIn('id', $value)->get();

                                // Consultamos las disponibilidades existentes para este servicio
                                $query = ServicesAvailability::where('services_id', $serviceId);

                                // Si estamos editando un registro, excluimos el registro actual
                                if ($record) {
                                    $query->where('id', '!=', $record->id);
                                }

                                $existingAvailabilities = $query->get();

                                // Verificamos cada bloque de tiempo seleccionado
                                foreach ($selectedTimeBlocks as $timeBlock) {
                                    foreach ($existingAvailabilities as $existing) {
                                        // Obtenemos los IDs de los bloques de tiempo existentes
                                        $existingBlockIds = $existing->time_block_ids;

                                        // Si es una cadena JSON, la decodificamos
                                        if (is_string($existingBlockIds)) {
                                            $existingBlockIds = json_decode($existingBlockIds, true);
                                        }

                                        // Si no es un array después de la decodificación, continuamos
                                        if (!is_array($existingBlockIds)) {
                                            continue;
                                        }

                                        // Verificamos si el bloque actual está en la lista de bloques existentes
                                        if (in_array($timeBlock->id, $existingBlockIds)) {
                                            $dayName = match ($selectedDay) {
                                                'monday' => 'Lunes',
                                                'tuesday' => 'Martes',
                                                'wednesday' => 'Miércoles',
                                                'thursday' => 'Jueves',
                                                'friday' => 'Viernes',
                                                default => $selectedDay,
                                            };

                                            $fail("El servicio ya tiene disponibilidad para {$dayName} en el horario {$timeBlock->hours}.");
                                            return;
                                        }
                                    }
                                }
                            };
                        }
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('service.name')
                    ->label('Servicio')
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
                    ->sortable(),

                TextColumn::make('time_block_ids')
                    ->label('Días y Horas')
                    ->formatStateUsing(function ($state, ServicesAvailability $record): string {
                        // Obtener los bloques de tiempo usando el método del modelo
                        $timeBlocks = $record->getTimeBlocks();

                        if ($timeBlocks->isEmpty()) {
                            return 'Sin horarios asignados';
                        }

                        // Todos deberían ser del mismo día, pero por si acaso agrupamos por día
                        $blocksByDay = $timeBlocks->groupBy('day_of_week');

                        $result = '';
                        foreach ($blocksByDay as $day => $blocks) {
                            $dayName = match ($day) {
                                'monday' => 'Lunes',
                                'tuesday' => 'Martes',
                                'wednesday' => 'Miércoles',
                                'thursday' => 'Jueves',
                                'friday' => 'Viernes',
                                default => $day,
                            };

                            $hoursText = $blocks->map(function ($block) {
                                return match ($block->hours) {
                                    '08:00-10:00' => '8:00 AM - 10:00 AM',
                                    '10:00-12:00' => '10:00 AM - 12:00 AM',
                                    '12:00-14:00' => '12:00 AM - 14:00 AM',
                                    '14:00-16:00' => '14:00 AM - 16:00 PM',
                                    '14:00-18:00' => '14:00 PM - 18:00 PM',
                                    default => $block->hours,
                                };
                            })->implode(', ');

                            $result .= "<strong>{$dayName}:</strong> {$hoursText}<br>";
                        }

                        return $result;
                    })
                    ->html()
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service')
                    ->relationship('service', 'name')
                    ->label('Servicio'),

                Tables\Filters\SelectFilter::make('day_of_week')
                    ->label('Día')
                    ->options([
                        'monday' => 'Lunes',
                        'tuesday' => 'Martes',
                        'wednesday' => 'Miércoles',
                        'thursday' => 'Jueves',
                        'friday' => 'Viernes',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            function (Builder $query, $dayOfWeek): Builder {
                                // Obtener los IDs de bloques de tiempo para ese día
                                $timeBlockIds = TimeBlock::where('day_of_week', $dayOfWeek)->pluck('id');

                                // Consulta para encontrar registros que contengan al menos uno de estos IDs
                                return $query->whereHas('timeBlocks', function (Builder $query) use ($dayOfWeek) {
                                    $query->where('day_of_week', $dayOfWeek);
                                });
                            }
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServicesAvailabilities::route('/'),
            'create' => Pages\CreateServicesAvailability::route('/create'),
            'edit' => Pages\EditServicesAvailability::route('/{record}/edit'),
        ];
    }
}
