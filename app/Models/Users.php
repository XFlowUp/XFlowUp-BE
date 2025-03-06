<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

/**
 * @property string $email The user's email address.
 * @property string $profile_pic_url The URL of the user's profile picture.
 * @property int $plan_id The ID of the user's subscription plan.
 * @property string $github_id The user's GitHub ID.
 * @method static createOrUpdate(array $attributes, array $values = []) Create or update a user.
 * @return Users
 */
class Users extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = "users";

    protected $fillable = [
        'email',
        'profile_pic_url',
        'plan_id',
        'github_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plans::class, 'plan_id');
    }

    public function githubToken(): HasOne
    {
        return $this->hasOne(GithubToken::class, 'user_id');
    }

    public function setGithubId($githubId)
    {
        $this->github_id = $githubId;
        $this->save();
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
