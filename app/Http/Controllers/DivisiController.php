<?php

namespace App\Http\Controllers;

use App\Divisi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DivisiController extends Controller
{
    public function getDivisi($id)
    {
        // $query = Divisi::with('project')->find($id);
        $query = Divisi::with(['project' => function($data) {
            $data->orderBy('tanggal_mulai', 'desc');
            $data->where('flag_mcs', 5);
        }, 'project.consultant'])->find($id);

        $thn_mulai = array();
        foreach ($query->project as $s) {
            $pisah = Carbon::parse($s->tanggal_mulai);
            if (isset($thn_mulai[$pisah->year])) {
                $thn_mulai[$pisah->year] += 1 ;
            } else {
                $thn_mulai[$pisah->year] = 1 ;
            }
        }
        // return $query;

        try {
            $data['message']    =   'GET Berhasil.';
            $data['data']       =   $query;
            $data['tahun']      =   $thn_mulai;
            return response()->json([
                "status"    => 1,
                "data"      => $data,
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   'GET Gagal';
            return response()->json([
                'status'    =>  0,
                'data'      =>  $data
            ],200);
        }
    }

    public function getDivisiByDirektorat($sample)
    {
        try {
            $sample = str_replace('-', ' ', $sample);

            if ($sample === 'NULL') {
                $query = Divisi::where('direktorat',NULL)->get();
            }else{
                $query = Divisi::where('direktorat',$sample)->get();
            }

            $data['message']    =   'GET Berhasil.';
            $data['data']       =   $query;
            return response()->json([
                "status"    => 1,
                "data"      => $data,
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   'GET Gagal';
            return response()->json([
                'status'    =>  0,
                'data'      =>  $data
            ],200);
        }
    }

    public function getALlDivisi(){
        try{
            $query              = Divisi::select('direktorat')->groupBy('direktorat')->get();
            $queryDiv           = Divisi::get();
            $data['message']    =   'GET Berhasil.';
            $data['direktorat'] =   $query;
            $data['divisi']     = $queryDiv;
            return response()->json([
                "status"    => 1,
                "data"      => $data,
            ],200);
        }catch (\Throwable $th){
            $data['message']    =   'GET Gagal';
            return response()->json([
                'status'    =>  0,
                'data'      =>  $data
            ],200);
        }
    }
}
