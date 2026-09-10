<?php

namespace App\Filament\Pages;

use App\Imports\FoodDataImport;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class ImportFoodData extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string|null|\UnitEnum $navigationGroup = '食物管理';

    protected static ?string $title = '导入食物数据';

    protected string $view = 'filament.pages.import-food-data';

    public ?array $data = [];

    public int $imported = 0;

    public int $skipped = 0;

    public int $failed = 0;

    public bool $isProcessing = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                FileUpload::make('file')
                    ->label('上传文件')
                    ->acceptedFileTypes(['text/csv', 'application/json', 'text/plain'])
                    ->maxSize(10240)
                    ->required()
                    ->directory('food-imports'),
                Select::make('format')
                    ->label('文件格式')
                    ->options([
                        'csv' => 'CSV',
                        'json' => 'JSON',
                    ])
                    ->default('csv')
                    ->required(),
                Select::make('source')
                    ->label('数据来源')
                    ->options([
                        'crawled' => '爬取数据',
                        'official' => '官方数据',
                        'user_custom' => '用户自定义',
                    ])
                    ->default('crawled')
                    ->required(),
                Toggle::make('dry_run')
                    ->label('预览模式（不实际导入）')
                    ->default(false),
            ])
            ->statePath('data');
    }

    public function mount(): void
    {
        $this->form->fill([
            'format' => 'csv',
            'source' => 'crawled',
            'dry_run' => false,
        ]);
    }

    public function import(): void
    {
        $this->isProcessing = true;

        try {
            $data = $this->form->getState();
            $file = $data['file'];
            $format = $data['format'];
            $source = $data['source'];
            $dryRun = $data['dry_run'];

            $filePath = is_string($file) ? $file : $file->getRealPath();

            $import = new FoodDataImport($source, $dryRun);
            $import->import($filePath, $format);

            $this->imported = $import->getImportedCount();
            $this->skipped = $import->getSkippedCount();
            $this->failed = $import->getFailedCount();

            Notification::make()
                ->title('导入完成')
                ->body("成功: {$this->imported}, 跳过: {$this->skipped}, 失败: {$this->failed}")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('导入失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->isProcessing = false;
        }
    }
}
