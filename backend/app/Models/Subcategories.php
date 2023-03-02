<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcategories extends Model
{

      /**
       * The attributes that are mass assignable.
       *
       * @var array
       */
      protected $fillable = [
          'name','desc',"categories_id","status"
      ];

      /**
       * The attributes that should be hidden for arrays.
       *
       * @var array
       */
      protected $hidden = [];

      public function categories()
      {
          return $this->belongsTo('App\Models\Categories');
      }
}
