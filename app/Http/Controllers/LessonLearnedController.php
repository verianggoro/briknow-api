<?php

namespace App\Http\Controllers;

use App\Lesson_learned;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LessonLearnedController extends Controller
{
    public function getAll(Request $request){
        try{
            $validator= Validator::make($request-> all(), [
                'tahap' => 'nullable',
            ]);

            if (empty($request->tahap)){
                $query = Project::with(['lesson_learned'])->get();
            }else{
                $tahp = $request->tahap;
                $query = Project::whereHas('lesson_learned', function ($q) use ($tahp) {
                    $q->where('tahap', '=', $tahp);
                })->get();
            }
            return response()->json([
                "message"   => "GET Berhasil",
                "status"    => 1,
                "data"      => $query
            ],200);
        }catch (\Throwable $th){
            $datas['message']    =   'GET Gagal';
            return response()->json([
                'status'    =>  0,
                'data'      =>  $datas
            ],200);
        }
    }
}
