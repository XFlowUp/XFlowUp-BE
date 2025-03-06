<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProjectMembers
 *
 * @property int $project_id The ID of the project.
 * @property int $user_id The ID of the user.
 */
class ProjectMembers extends Model
{
    protected $table = "project_members";

    protected $fillable = [
        'project_id',
        'user_id'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
