<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
