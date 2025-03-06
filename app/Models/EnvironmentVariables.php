<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class EnvironmentVariables
 *
 * @property int $environment_variable_list_id The ID of the environment variable list.
 * @property string $key The key of the environment variable.
 * @property string $value The value of the environment variable.
 */
class EnvironmentVariables extends Model
{
    protected $table = "environment_variables";

    protected $fillable = [
        "environment_variable_list_id",
        "key",
        "value"
    ];

    public function environmentList(): BelongsTo
    {
        return $this->belongsTo(EnvironmentLists::class, 'environment_variable_list_id');
    }

    public function project(): BelongsTo
    {
        return $this->environmentList->project();
    }
}
