<?php

namespace App\Filament\Resources\AdminManagement\EvaluationResource\Pages;

use App\Filament\Resources\AdminManagement\EvaluationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEvaluation extends CreateRecord
{
    protected static string $resource = EvaluationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
