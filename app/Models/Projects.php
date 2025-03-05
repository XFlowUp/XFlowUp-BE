<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Projects extends Model
{
    protected $table = "projects";

    protected $fillable = [
        'name',
        'repository',
        'slug',
        'is_deleted'
    ];

    public static function generateSlug($length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        if (Projects::where('slug', $randomString)->exists()) {
            return Projects::generateSlug();
        }
        return $randomString;
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($project) {
            $project->slug = Projects::generateSlug();
        });
    }

    public function members()
    {
        return $this->hasMany(ProjectMembers::class, 'project_id');
    }
}
