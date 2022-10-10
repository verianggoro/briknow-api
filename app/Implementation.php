<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Implementation extends Model {
    protected $table = 'implementation';

    protected $fillable =
        ['id',
        'title',
        'slug',
        'project_managers_id',
        'status',
        'views',
        'thumbnail',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_restricted',
        'user_access',
        'desc_piloting',
        'desc_roll_out',
        'desc_sosialisasi',
        'project_id',
        'user_checker',
        'user_signer',
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

    protected $with = ['attach_file', 'project', 'project_managers', 'userchecker', 'usersigner'];

    public function attach_file() {
        return $this->hasMany(AttachFile::class, 'implementation_id');
    }

    public function project() {
        return $this->belongsTo(Projects::class,'project_id');
    }

    public function project_managers(){
        return $this->belongsTo(Project_managers::class,'project_managers_id');
    }

    public function divisi(){
        return $this->belongsTo(Divisi::class,'divisi_id');
    }

    public function userchecker(){
        return $this->belongsTo(User::class,'user_checker','personal_number');
    }

    public function usersigner(){
        return $this->belongsTo(User::class,'user_signer','personal_number');
    }

}