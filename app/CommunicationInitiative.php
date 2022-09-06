<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommunicationInitiative extends Model {
    protected $table = 'communication_initiative';

    protected $fillable = ['id',
        'title',
        'slug',
        'type_file',
        'desc',
        'status',
        'views',
        'thumbnail',
        'approve_at',
        'publish_at',
        'reject_at',
        'deleted_at'];

}