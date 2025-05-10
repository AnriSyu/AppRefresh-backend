<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Notifications;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NotificationResource extends Resource
{
    protected static ?string $model = Notifications::class;
    protected static ?string $navigationGroup = 'Sistema';


    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('message')
                    ->label('Mensaje')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'info' => 'Información',
                        'warning' => 'Advertencia',
                        'error' => 'Error',
                    ])
                    ->required(),

                    Forms\Components\Select::make('users')
                    ->label('Usuarios')
                    ->multiple()
                    ->relationship('users', 'name', function ($query) {
                        return $query->whereDoesntHave('roles', function ($q) {
                            $q->where('name', 'Administrador');
                        });
                    })
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),

                Tables\Columns\TextColumn::make('message')
                    ->label('Mensaje')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'info',
                        'warning' => 'warning',
                        'danger' => 'error',
                    ]),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Destinatarios')
                    ->counts('users'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('unread')
                    ->label('No leídas')
                    ->query(fn (Builder $query): Builder => $query->whereHas('users', function ($q) {
                        $q->where('notification_user.is_read', false);
                    })),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'info' => 'Información',
                        'warning' => 'Advertencia',
                        'error' => 'Error',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}
