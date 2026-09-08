<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FoodItemResource\Pages;
use App\Models\FoodItem;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class FoodItemResource extends Resource
{
    protected static ?string $model = FoodItem::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-bolt';

    protected static string|null|\UnitEnum $navigationGroup = '食物管理';

    protected static ?string $modelLabel = '食物';

    protected static ?string $pluralModelLabel = '食物库';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('基本信息')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->label('名称'),
                    TextInput::make('aliases')
                        ->label('别名（JSON数组）'),
                    Select::make('category')
                        ->required()
                        ->options([
                            '主食' => '主食',
                            '蔬菜' => '蔬菜',
                            '水果' => '水果',
                            '肉类' => '肉类',
                            '蛋奶' => '蛋奶',
                            '海鲜' => '海鲜',
                            '零食' => '零食',
                            '饮料' => '饮料',
                            '调味品' => '调味品',
                            '坚果' => '坚果',
                            '豆制品' => '豆制品',
                            '其他' => '其他',
                        ])
                        ->label('分类'),
                ])->columns(2),

            Section::make('营养数据（每100g）')
                ->schema([
                    TextInput::make('calories_per_100g')
                        ->required()
                        ->numeric()
                        ->label('热量 (kcal)'),
                    TextInput::make('protein_per_100g')
                        ->numeric()
                        ->label('蛋白质 (g)'),
                    TextInput::make('carbs_per_100g')
                        ->numeric()
                        ->label('碳水 (g)'),
                    TextInput::make('fat_per_100g')
                        ->numeric()
                        ->label('脂肪 (g)'),
                ])->columns(4),

            Section::make('其他信息')
                ->schema([
                    TextInput::make('serving_size')
                        ->numeric()
                        ->default(100)
                        ->label('默认份量 (g)'),
                    TextInput::make('serving_unit')
                        ->default('g')
                        ->label('份量单位'),
                    Select::make('source')
                        ->options([
                            'crawled' => '爬取',
                            'official' => '官方',
                            'user_custom' => '用户自定义',
                        ])
                        ->label('来源'),
                    Select::make('review_status')
                        ->options([
                            'pending' => '待审核',
                            'approved' => '已通过',
                            'rejected' => '已拒绝',
                        ])
                        ->label('审核状态'),
                    Toggle::make('is_user_custom')
                        ->label('用户自定义'),
                    TextInput::make('version')
                        ->numeric()
                        ->default(1)
                        ->label('版本号'),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('calories_per_100g')
                    ->sortable()
                    ->suffix(' kcal'),
                Tables\Columns\TextColumn::make('protein_per_100g')
                    ->label('蛋白质')
                    ->suffix('g'),
                Tables\Columns\TextColumn::make('source')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'crawled' => 'warning',
                        'official' => 'success',
                        'user_custom' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('review_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        '主食' => '主食',
                        '蔬菜' => '蔬菜',
                        '水果' => '水果',
                        '肉类' => '肉类',
                        '其他' => '其他',
                    ]),
                Tables\Filters\SelectFilter::make('review_status')
                    ->options([
                        'pending' => '待审核',
                        'approved' => '已通过',
                        'rejected' => '已拒绝',
                    ]),
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'crawled' => '爬取',
                        'official' => '官方',
                        'user_custom' => '用户自定义',
                    ]),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFoodItems::route('/'),
            'create' => Pages\CreateFoodItem::route('/create'),
            'edit' => Pages\EditFoodItem::route('/{record}/edit'),
        ];
    }
}
