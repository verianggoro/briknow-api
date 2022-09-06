<?php

namespace App\Http\Controllers;

use App\Lesson_learned;
use App\Project;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;

class MyLessonLearnedController extends Controller
{
    public function index(){
        if (Auth::User()->role == 0) {
            $query = Lesson_learned::join('projects', 'projects.id', '=', 'lesson_learneds.project_id')
                ->join('divisis', 'divisis.id', '=', 'lesson_learneds.divisi_id')
                ->join('consultants', 'consultants.id', '=', 'lesson_learneds.consultant_id')
                ->where('lesson_learneds.user_maker', Auth::user()->personal_number)
                ->select(DB::raw("lesson_learneds.id, divisis.direktorat, divisis.divisi, projects.nama as project_name, consultants.nama as consultant_name,
                        lesson_learneds.tahap, lesson_learneds.lesson_learned, lesson_learneds.detail, lesson_learneds.created_at,
                        lesson_learneds.updated_at"))
                ->orderBy('lesson_learneds.created_at', 'DESC')
                ->paginate(10);
        }elseif(Auth::User()->role == 3){
            $query = Lesson_learned::join('projects', 'projects.id', '=', 'lesson_learneds.project_id')
                ->join('divisis', 'divisis.id', '=', 'lesson_learneds.divisi_id')
                ->join('consultants', 'consultants.id', '=', 'lesson_learneds.consultant_id')
                ->select(DB::raw("lesson_learneds.id, divisis.direktorat, divisis.divisi, projects.nama as project_name, consultants.nama as consultant_name,
                        lesson_learneds.tahap, lesson_learneds.lesson_learned, lesson_learneds.detail, lesson_learneds.created_at,
                        lesson_learneds.updated_at"))
                ->orderBy('lesson_learneds.created_at', 'DESC')
                ->paginate(10);
        }
        try {
            $data['message']    =   'GET Berhasil.';
            $data['data']       =   $query;
            return response()->json([
                "status"    => 1,
                "data"      => $data
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   'GET Gagal';
            return response()->json([
                'status'    =>  0,
                'data'      =>  $data
            ],200);
        }

    }
}
