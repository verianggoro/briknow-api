<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Http\Request;
use stdClass;

class DownloadController extends Controller
{
    public $successStatus = 200;

    public function searchproject(){
        $cari = request()->searchTerm;
        if ($cari == null) {
            $hasil = Project::orderBy('nama', 'asc')->limit(8)->where('flag_mcs',5)->get();
        }else{
            $hasil = Project::where('nama', 'like', "%$cari%")->orderBy('nama', 'asc')->where('flag_mcs',5)->limit(8)->get();
            // $hasil = Project::where('nama', 'like', "%$cari%")->orderBy('nama', 'asc')->limit(8)->get();
        }

        $data = [];
        foreach ($hasil as $key) {
            $data[]         = array("id"=>$key->id, "text"=>$key->nama);
        }

        return json_encode($data);
    }

    public function detailproject($id){
        try {
            $result = Project::with(['user_restrict'])->where('id',$id)->with('consultant','divisi','project_managers','document')->where('flag_mcs',5)->first();
            return response()->json([
                "status"  =>  1,
                "data"    =>  $result
            ],200);
        } catch (\Throwable $th) {
            $result['message']  =   "Something Error";
            return response()->json([
                "status"  =>  0,
                "data"    =>  $result
            ],200);
        }
    }
}
