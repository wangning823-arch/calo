<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-users';

    protected static string|null|\UnitEnum $navigationGroup = '用户管理';

    protected static ?string $modelLabel = '用户';

    protected static ?string $pluralModelLabel = '用户列表';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('height')
                    ->sortable()
                    ->suffix('cm'),
                Tables\Columns\TextColumn::make('activity_level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sedentary' => 'gray',
                        'light' => 'info',
                        'moderate' => 'success',
                        'heavy' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('cancelled_at')
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($state) => $state ? 'danger' : null),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->options([
                        'male' => '男',
                        'female' => '女',
                    ]),
                Tables\Filters\SelectFilter::make('activity_level')
                    ->options([
                        'sedentary' => '久坐',
                        'light' => '轻度',
                        'moderate' => '中度',
                        'heavy' => '重度',
                    ]),
                Tables\Filters\Filter::make('cancelled_at')
                    ->label('已注销')
                    ->query(fn ($query) => $query->whereNotNull('cancelled_at')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
        ];
    }
}
