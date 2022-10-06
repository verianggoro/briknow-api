<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommunicationSupport extends Model {
    protected $table = 'communication_support';

    protected $fillable = ['id',
        'project_id',
        'divisi_id',
        'title',
        'slug',
        'type_file',
        'desc',
        'status',
        'tanggal_mulai',
        'tanggal_selesai',
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

    protected $with = ['attach_file', 'divisi'];

    public function project(){
        return $this->belongsTo(Project::class,'id');
    }

    public function attach_file() {
        return $this->hasMany(AttachFile::class, 'com_id');
    }
    public function divisi(){
        return $this->belongsTo(Divisi::class,'divisi_id');
    }


}