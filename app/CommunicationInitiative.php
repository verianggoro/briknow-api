<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommunicationInitiative extends Model {
    protected $table = 'communication_initiative';

    protected $fillable = ['id',
        'project_id',
        'title',
        'slug',
        'type_file',
        'desc',
        'status',
        'views',
        'thumbnail',
        'user_maker',
        'updated_by',
        'approve_at',
        'approve_by',
        'publish_at',
        'publish_by',
        'unpublish_at',
        'unpublish_by',
        'reject_at',
        'reject_by',
        'deleted_at',
        'deleted_by'];

//    protected $with = ['project'];

    public function project(){
        return $this->belongsTo(Project::class,'project_id');
    }

}