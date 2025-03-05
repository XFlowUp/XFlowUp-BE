<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectEnvironments extends Model
{
    protected $table = "project_environments";

    protected $fillable = [
        "project_id",
        "name",
        "description",
        "branch",
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function environmentList()
    {
        return $this->hasOne(EnvironmentLists::class, 'project_environment_id');
    }
}
