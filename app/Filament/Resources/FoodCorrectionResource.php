<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FoodCorrectionResource\Pages;
use App\Models\FoodCorrection;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class FoodCorrectionResource extends Resource
{
    protected static ?string $model = FoodCorrection::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-pencil-square';

    protected static string|null|\UnitEnum $navigationGroup = '食物管理';

    protected static ?string $modelLabel = '食物纠错';

    protected static ?string $pluralModelLabel = '食物纠错';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make()
                ->schema([
                    Select::make('review_status')
                        ->options([
                            'pending' => '待审核',
                            'approved' => '已通过',
                            'rejected' => '已拒绝',
                        ])
                        ->label('审核状态')
                        ->required(),
                    DateTimePicker::make('reviewed_at')
                        ->label('审核时间'),
                ])->columns(2),

            Section::make()
                ->schema([
                    Textarea::make('correction_content')
                        ->label('纠错内容')
                        ->required()
                        ->rows(5),
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
                Tables\Columns\TextColumn::make('food.name')
                    ->label('食物')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('提交用户')
                    ->searchable(),
                Tables\Columns\TextColumn::make('correction_content')
                    ->label('纠错内容')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('review_status')
                    ->label('审核状态')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => '待审核',
                        'approved' => '已通过',
                        'rejected' => '已拒绝',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('review_status')
                    ->label('审核状态')
                    ->options([
                        'pending' => '待审核',
                        'approved' => '已通过',
                        'rejected' => '已拒绝',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFoodCorrections::route('/'),
            'edit' => Pages\EditFoodCorrection::route('/{record}/edit'),
        ];
    }
}
