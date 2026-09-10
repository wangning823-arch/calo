<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeedbackResource\Pages;
use App\Models\Feedback;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static string|null|\UnitEnum $navigationGroup = '用户管理';

    protected static ?string $modelLabel = '反馈';

    protected static ?string $pluralModelLabel = '用户反馈';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make()
                ->schema([
                    Select::make('type')
                        ->options([
                            'bug' => 'Bug报告',
                            'suggestion' => '功能建议',
                            'other' => '其他',
                        ])
                        ->label('类型')
                        ->required(),
                    Select::make('status')
                        ->options([
                            'open' => '待处理',
                            'processing' => '处理中',
                            'resolved' => '已解决',
                        ])
                        ->label('状态')
                        ->required(),
                ])->columns(2),

            Section::make()
                ->schema([
                    Textarea::make('content')
                        ->label('内容')
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('用户')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('类型')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bug' => 'Bug报告',
                        'suggestion' => '功能建议',
                        'other' => '其他',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'bug' => 'danger',
                        'suggestion' => 'warning',
                        'other' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('content')
                    ->label('内容')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => '待处理',
                        'processing' => '处理中',
                        'resolved' => '已解决',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'processing' => 'info',
                        'resolved' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('类型')
                    ->options([
                        'bug' => 'Bug报告',
                        'suggestion' => '功能建议',
                        'other' => '其他',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('状态')
                    ->options([
                        'open' => '待处理',
                        'processing' => '处理中',
                        'resolved' => '已解决',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeedbacks::route('/'),
            'edit' => Pages\EditFeedback::route('/{record}/edit'),
        ];
    }
}
