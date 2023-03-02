<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipments extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name','desc','category_id','subcategory_id','type_id','special_instructions','allocation_start_date','allocation_end_date','allocation_time','contact_no','email_id','rating','status','images','image_uid','video_link','status'
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [];

  public function category(){
     return $this->belongsTo('App\Models\Categories');
  }

  public function type(){
     return $this->belongsTo('App\Models\Types');
  }

}
