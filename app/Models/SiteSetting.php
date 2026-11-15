<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Key/value store for the editable site settings.
 * Always read and write through App\Services\SettingsService (cached).
 */
#[Fillable(['group', 'key', 'value'])]
class SiteSetting extends Model {}
