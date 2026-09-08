<?php

namespace App\Filament\Resources\TrainingPlanResource\Pages;

use App\Filament\Resources\TrainingPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrainingPlans extends ListRecords
{
    protected static string $resource = TrainingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
