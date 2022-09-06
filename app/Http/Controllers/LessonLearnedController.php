<?php

namespace App\Http\Controllers;

use App\Lesson_learned;
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
                $query = Lesson_learned::join('projects', 'projects.id', '=', 'lesson_learneds.project_id')
                    ->join('divisis', 'divisis.id', '=', 'lesson_learneds.divisi_id')
                    ->join('consultants', 'consultants.id', '=', 'lesson_learneds.consultant_id')
                    ->select(DB::raw("lesson_learneds.id, divisis.direktorat, divisis.divisi, projects.nama as project_name, consultants.nama as consultant_name,
                        lesson_learneds.tahap, lesson_learneds.lesson_learned, lesson_learneds.detail, lesson_learneds.created_at,
                        lesson_learneds.updated_at"))
                    ->paginate(10);
            }else{
                $query = Lesson_learned::join('projects', 'projects.id', '=', 'lesson_learneds.project_id')
                    ->join('divisis', 'divisis.id', '=', 'lesson_learneds.divisi_id')
                    ->join('consultants', 'consultants.id', '=', 'lesson_learneds.consultant_id')
                    ->where('lesson_learneds.tahap', '=', $request->tahap)
                    ->select(DB::raw("lesson_learneds.id, divisis.direktorat, divisis.divisi, projects.nama as project_name, consultants.nama as consultant_name,
                        lesson_learneds.tahap, lesson_learneds.lesson_learned, lesson_learneds.detail, lesson_learneds.created_at,
                        lesson_learneds.updated_at"))
                    ->paginate(10);
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
