<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    public function links(){
    	return $this->hasMany('App\Link', 'campaign_id');
    }
}
