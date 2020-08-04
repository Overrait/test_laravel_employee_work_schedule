<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductionCalendar extends Model
{
    protected $fillable = array(
        'day',
        'working',
        'holiday',
        'shortened'
    );

    public $timestamps = false;
    //
}