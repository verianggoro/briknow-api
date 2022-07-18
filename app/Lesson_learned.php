<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lesson_learned extends Model
{
    protected $fillable = ['project_id','lesson_learned','detail'];
    public $timestamps = false;

    protected $mapping = [
        'properties'    =>  [
            'project_id'=>[
                'type' =>  'integer',
                'analyzer'  =>  'not_analyzed'
            ],
            'lesson_learned' => [                
                'type' =>  'text',
                'analyzer' => 'english'
            ],
            'detail' => [
                'type' => 'string',
                'analyzer' => 'english'
            ]
        ]
    ];

    public function project(){
        return $this->belongsTo(Project::class,'project_id');
    }
}
