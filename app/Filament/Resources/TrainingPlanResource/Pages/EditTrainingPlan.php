<?php

namespace App\Filament\Resources\TrainingPlanResource\Pages;

use App\Filament\Resources\TrainingPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrainingPlan extends EditRecord
{
    protected static string $resource = TrainingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
