<?php

namespace App\Policies;

class MethodologyPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'methodologies';
    }
}
