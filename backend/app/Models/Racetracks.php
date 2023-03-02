<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Racetracks extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
      protected $fillable = ['name','desc','type_id','image_uid','distance','address','status','images','video_link','allocation_start_date','allocation_end_date','allocation_time','owner_id','owner_email','owner_contact','responsible_person','rating','shared', 'is_featured','city'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function racetrack_equipments(){
        return $this->hasMany('App\Models\Racetrack_equipments');
    }

    public function racetrack_servicemans(){
        return $this->hasMany('App\Models\Racetrack_servicemans');
    }

    public function owner(){
        return $this->belongsTo('App\Models\Users');
    }

    public function type(){
        return $this->belongsTo('App\Models\Types');
    }

}
