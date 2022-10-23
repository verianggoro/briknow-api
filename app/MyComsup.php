<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MyComsup extends Model
{
    protected $fillable = [
        'user_id', 'comsup_id'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function communication_support()
    {
        return $this->belongsTo(CommunicationSupport::class, 'id');
    }
}
