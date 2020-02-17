<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    public function link_visits(){
    	return $this->hasMany('App\LinkVisit', 'link_id');
    }

    public function campaign(){
        return $this->belongsTo('App\Campaign', 'campaign_id');
    }
}
