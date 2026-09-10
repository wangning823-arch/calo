<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-document-text';

    protected static string|null|\UnitEnum $navigationGroup = '系统管理';

    protected static ?string $modelLabel = '审计日志';

    protected static ?string $pluralModelLabel = '审计日志';

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
                Tables\Columns\TextColumn::make('user_id')
                    ->sortable()
                    ->label('用户ID'),
                Tables\Columns\TextColumn::make('action_type')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'login') => 'success',
                        str_contains($state, 'password') => 'warning',
                        str_contains($state, 'cancel') => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('ip_address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action_type')
                    ->options([
                        'login' => '登录',
                        'logout' => '登出',
                        'password_reset' => '密码重置',
                        'password_changed' => '密码修改',
                        'login_locked' => '登录锁定',
                        'cancellation_requested' => '注销申请',
                        'cancellation_revoked' => '注销撤销',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from'),
                        \Filament\Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
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
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}
