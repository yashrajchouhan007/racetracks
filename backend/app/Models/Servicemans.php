<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicemans extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = ['name','contact_no','email_id', 'address', 'specializations','available_hours','rating','status'];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [];
}
