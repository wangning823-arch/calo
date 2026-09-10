<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeResource\Pages;
use App\Models\Recipe;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-bookmark';

    protected static string|null|\UnitEnum $navigationGroup = '内容管理';

    protected static ?string $modelLabel = '食谱';

    protected static ?string $pluralModelLabel = '食谱库';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('基本信息')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->label('标题'),
                    \Filament\Forms\Components\Select::make('meal_type')
                        ->required()
                        ->options([
                            'breakfast' => '早餐',
                            'lunch' => '午餐',
                            'dinner' => '晚餐',
                            'snack' => '加餐',
                        ])
                        ->label('餐次'),
                    \Filament\Forms\Components\Select::make('status')
                        ->required()
                        ->options([
                            'draft' => '草稿',
                            'published' => '已发布',
                            'archived' => '已归档',
                        ])
                        ->label('状态'),
                ])->columns(3),

            Section::make('营养数据')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('total_calories')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->label('总热量 (kcal)'),
                    \Filament\Forms\Components\TextInput::make('protein')
                        ->numeric()
                        ->minValue(0)
                        ->label('蛋白质 (g)'),
                    \Filament\Forms\Components\TextInput::make('carbs')
                        ->numeric()
                        ->minValue(0)
                        ->label('碳水 (g)'),
                    \Filament\Forms\Components\TextInput::make('fat')
                        ->numeric()
                        ->minValue(0)
                        ->label('脂肪 (g)'),
                ])->columns(4),

            Section::make('食材与步骤')
                ->schema([
                    Repeater::make('ingredients')
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('name')
                                ->label('食材名'),
                            \Filament\Forms\Components\TextInput::make('amount')
                                ->label('用量'),
                        ])
                        ->columns(2)
                        ->label('食材清单'),
                    Repeater::make('steps')
                        ->schema([
                            \Filament\Forms\Components\Textarea::make('step')
                                ->label('步骤')
                                ->rows(2),
                        ])
                        ->label('制作步骤'),
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
                Tables\Columns\TextColumn::make('meal_type')
                    ->label('餐次')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'breakfast' => '早餐',
                        'lunch' => '午餐',
                        'dinner' => '晚餐',
                        'snack' => '加餐',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'breakfast' => 'info',
                        'lunch' => 'success',
                        'dinner' => 'warning',
                        'snack' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_calories')
                    ->label('总热量')
                    ->sortable()
                    ->suffix(' kcal'),
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
                Tables\Filters\SelectFilter::make('meal_type')
                    ->label('餐次')
                    ->options([
                        'breakfast' => '早餐',
                        'lunch' => '午餐',
                        'dinner' => '晚餐',
                        'snack' => '加餐',
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
                    ->action(fn (Recipe $record) => $record->update(['status' => 'published']))
                    ->visible(fn (Recipe $record) => $record->status !== 'published'),
                Actions\Action::make('archive')
                    ->label('归档')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (Recipe $record) => $record->update(['status' => 'archived']))
                    ->visible(fn (Recipe $record) => $record->status !== 'archived'),
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
            'index' => Pages\ListRecipes::route('/'),
            'create' => Pages\CreateRecipe::route('/create'),
            'edit' => Pages\EditRecipe::route('/{record}/edit'),
        ];
    }
}
