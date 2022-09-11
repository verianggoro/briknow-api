<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Implementation extends Model {
    protected $table = 'implementation';

    protected $fillable =
        ['id',
        'title',
        'slug',
        'divisi_id',
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
        'project_link',
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

}