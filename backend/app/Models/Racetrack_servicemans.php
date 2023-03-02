<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Racetrack_servicemans extends Model
{
        /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'racetracks_id','servicemans_id'
    ];

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

    public function servicemans()
    {
        return $this->belongsTo('App\Models\Servicemans');
    }
}
