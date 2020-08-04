<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vacation extends Model
{
    public $timestamps = false;
    //
    public function vacations() {
        return $this->belongsToMany('App\Vacation');
    }
}