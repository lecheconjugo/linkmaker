<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LinkVisit extends Model
{
    public function link(){
        return $this->belongsTo('App\Link', 'link_id');
    }
}
