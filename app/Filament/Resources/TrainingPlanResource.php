<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainingPlanResource\Pages;
use App\Models\TrainingPlan;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TrainingPlanResource extends Resource
{
    protected static ?string $model = TrainingPlan::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|null|\UnitEnum $navigationGroup = '内容管理';

    protected static ?string $modelLabel = '训练计划';

    protected static ?string $pluralModelLabel = '训练计划库';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('基本信息')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->label('标题'),
                    \Filament\Forms\Components\Select::make('goal')
                        ->required()
                        ->options([
                            'lose' => '减脂',
                            'shape' => '塑形',
                        ])
                        ->label('目标'),
                    \Filament\Forms\Components\Select::make('difficulty')
                        ->required()
                        ->options([
                            'beginner' => '新手',
                            'advanced' => '进阶',
                        ])
                        ->label('难度'),
                    \Filament\Forms\Components\TextInput::make('duration_weeks')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(52)
                        ->label('持续周数'),
                    \Filament\Forms\Components\Select::make('status')
                        ->required()
                        ->options([
                            'draft' => '草稿',
                            'published' => '已发布',
                            'archived' => '已归档',
                        ])
                        ->label('状态'),
                ])->columns(3),

            Section::make('训练内容')
                ->schema([
                    Repeater::make('exercises')
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('name')
                                ->label('动作名称'),
                            \Filament\Forms\Components\TextInput::make('sets')
                                ->label('组数'),
                            \Filament\Forms\Components\TextInput::make('reps')
                                ->label('次数'),
                            \Filament\Forms\Components\TextInput::make('rest')
                                ->label('休息时间'),
                        ])
                        ->columns(4)
                        ->label('训练动作'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('标题')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('goal')
                    ->label('目标')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'lose' => '减脂',
                        'shape' => '塑形',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'lose' => 'danger',
                        'shape' => 'info',
                    }),
                Tables\Columns\TextColumn::make('difficulty')
                    ->label('难度')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'beginner' => '新手',
                        'advanced' => '进阶',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'beginner' => 'success',
                        'advanced' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('duration_weeks')
                    ->label('持续周数')
                    ->sortable()
                    ->suffix(' 周'),
                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => '草稿',
                        'published' => '已发布',
                        'archived' => '已归档',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'archived' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('goal')
                    ->label('目标')
                    ->options([
                        'lose' => '减脂',
                        'shape' => '塑形',
                    ]),
                Tables\Filters\SelectFilter::make('difficulty')
                    ->label('难度')
                    ->options([
                        'beginner' => '新手',
                        'advanced' => '进阶',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('状态')
                    ->options([
                        'draft' => '草稿',
                        'published' => '已发布',
                        'archived' => '已归档',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
                Actions\Action::make('publish')
                    ->label('发布')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (TrainingPlan $record) => $record->update(['status' => 'published']))
                    ->visible(fn (TrainingPlan $record) => $record->status !== 'published'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainingPlans::route('/'),
            'create' => Pages\CreateTrainingPlan::route('/create'),
            'edit' => Pages\EditTrainingPlan::route('/{record}/edit'),
        ];
    }
}
