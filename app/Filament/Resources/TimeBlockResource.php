<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimeBlockResource\Pages;
use App\Models\TimeBlock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;

class TimeBlockResource extends Resource
{
    protected static ?string $model = TimeBlock::class;
    protected static ?string $navigationGroup = 'Programación';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Bloque de Tiempo';
    protected static ?string $pluralModelLabel = 'Bloques de Tiempo';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('day_of_week')
                    ->label('Día de la Semana')
                    ->options([
                        'monday' => 'Lunes',
                        'tuesday' => 'Martes',
                        'wednesday' => 'Miércoles',
                        'thursday' => 'Jueves',
                        'friday' => 'Viernes',
                    ])
                    ->required(),

                Select::make('hours')
                    ->label('Hora')
                    ->options([
                        '08:00-10:00' => '8:00 AM - 10:00 AM',
                        '10:00-12:00' => '10:00 AM - 12:00 PM',
                        '12:00-14:00' => '12:00 PM - 2:00 PM',
                        '14:00-16:00' => '2:00 PM - 4:00 PM',
                        '14:00-18:00' => '2:00 PM - 6:00 PM',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day_of_week')
                    ->label('Día de la Semana')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'monday' => 'Lunes',
                        'tuesday' => 'Martes',
                        'wednesday' => 'Miércoles',
                        'thursday' => 'Jueves',
                        'friday' => 'Viernes',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('hours')
                    ->label('Horas')
                    ->formatStateUsing(function ($state) {
                        if (is_string($state) && Str::isJson($state)) {
                            $state = json_decode($state, true);
                        }

                        if (!is_array($state)) {
                            return match ($state) {
                                '08:00-10:00' => '8:00 AM - 10:00 AM',
                                '10:00-12:00' => '10:00 AM - 12:00 PM',
                                '12:00-14:00' => '12:00 PM - 2:00 PM',
                                '14:00-16:00' => '2:00 PM - 4:00 PM',
                                '14:00-18:00' => '2:00 PM - 6:00 PM',
                                default => $state,
                            };
                        }

                        if (empty($state)) {
                            return 'Sin horas asignadas';
                        }

                        $formattedHours = array_map(fn ($hour) => match ($hour) {
                            '08:00-10:00' => '8:00 AM - 10:00 AM',
                            '10:00-12:00' => '10:00 AM - 12:00 PM',
                            '12:00-14:00' => '12:00 PM - 2:00 PM',
                            '14:00-16:00' => '2:00 PM - 4:00 PM',
                            '14:00-18:00' => '2:00 PM - 6:00 PM',
                            default => $hour,
                        }, $state);

                        return implode(', ', $formattedHours);
                    })
                    ->words(10)
                    ->wrap(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTimeBlocks::route('/'),
            'create' => Pages\CreateTimeBlock::route('/create'),
            'edit' => Pages\EditTimeBlock::route('/{record}/edit'),
        ];
    }
}
