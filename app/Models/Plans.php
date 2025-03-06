<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Plans
 *
 * @property int $id The ID of the plan.
 * @property string $name The name of the plan.
 * @property float $price The price of the plan.
 * @property int $duration The duration of the plan in months.
 * @property int $max_projects The maximum number of projects allowed.
 * @property int $max_deployments The maximum number of deployments allowed.
 *
 * @method Users[] users() Get the users associated with the plan.
 */
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

    public function users()
    {
        return $this->hasMany(Users::class, 'plan_id');
    }
};
