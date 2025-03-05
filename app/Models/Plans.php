<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plans extends Model
{
    protected $table = "plans";
    protected $fillable = [
        "name",
        "price",
        "duration",
        "max_projects",
        "max_deployments"
    ];

    public function users(){
        return $this->hasMany(User::class, 'plan_id');
    }
};


