<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AttachFile extends Model {
    protected $table = 'attach_file';

    protected $fillable = ['id',
        'com_id',
        'tipe',
        'nama',
        'jenis_file',
        'url_file',
        'size',
        ];

    public function communication_support(){
        return $this->belongsTo(CommunicationSupport::class, 'id');
    }
}