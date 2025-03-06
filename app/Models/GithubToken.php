<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GithubToken extends Model
{
    protected $table = "github_tokens";
    protected $primaryKey = "user_id";
    public $incrementing = false;

    protected $fillable = [
        "access_token",
        "refresh_token"
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
}
