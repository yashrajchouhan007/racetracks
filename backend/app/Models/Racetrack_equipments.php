<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Racetrack_equipments extends Model
{

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['racetracks_id','equipments_id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function racetracks()
    {
        return $this->belongsTo('App\Models\Racetracks');
    }

    public function equipments()
    {
        return $this->belongsTo('App\Models\Equipments');
    }

}
