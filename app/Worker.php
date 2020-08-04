<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    public $timestamps = false;
    //
    public function vacations() {
        return $this->hasMany('App\Vacation');
    }
}