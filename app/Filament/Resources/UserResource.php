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
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('姓名')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('手机号')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('性别')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => '男',
                        'female' => '女',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('height')
                    ->label('身高')
                    ->sortable()
                    ->suffix('cm'),
                Tables\Columns\TextColumn::make('activity_level')
                    ->label('活动水平')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sedentary' => '久坐',
                        'light' => '轻度',
                        'moderate' => '中度',
                        'heavy' => '重度',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'sedentary' => 'gray',
                        'light' => 'info',
                        'moderate' => 'success',
                        'heavy' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('cancelled_at')
                    ->label('注销时间')
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($state) => $state ? 'danger' : null),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('注册时间')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->label('性别')
                    ->options([
                        'male' => '男',
                        'female' => '女',
                    ]),
                Tables\Filters\SelectFilter::make('activity_level')
                    ->label('活动水平')
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
            ->actions([])
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
