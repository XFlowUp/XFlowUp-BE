<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvironmentLists extends Model
{
    protected $table = "environment_variables_list_of_project";

    protected $fillable = [
      "project_id",
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function environmentVariables()
    {
        return $this->hasMany(EnvironmentVariables::class, 'environment_variable_list_id');
    }

    public function projectEnvironment(): BelongsTo
    {
        return $this->belongsTo(ProjectEnvironments::class, 'project_environment_id');
    }
}
