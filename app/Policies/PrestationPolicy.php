<?php

namespace App\Policies;

class PrestationPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'prestations';
    }
}
