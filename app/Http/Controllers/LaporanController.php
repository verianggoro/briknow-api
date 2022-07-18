<?php

namespace App\Http\Controllers;

use App\ConsultantLog;
use App\ConsultantProject;
use App\Project;
use App\Search_log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use stdClass;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function proyektopfive(){
        try {
            // setting 12 bulan kebelakang
            $num= [];
            $label= [];
            $data=[];
            $vend = Project::withCount('search_log')->with('divisi','consultant')
            ->where('flag_mcs', 5)
            ->orderBy('search_log_count', 'desc')
            ->limit(5)
            ->get();
            // return response()->json($temp);
            foreach ($vend as $key) {
                $object = new stdClass;
                $object->namaproject = $key->nama??'-';
                $object->direktorat = $key->divisi->direktorat??'-';
                $object->divisi = $key->divisi->divisi??'-';
                $temp = "";
                foreach ($key->consultant as $item2) {
                    if ($temp === "") {
                        $temp = $item2->nama;
                    }else{
                        $temp .= ", ".$item2->nama;
                    }
                }
                $object->consultant = $temp??'-';
                $object->tanggalmulai = $key->tanggal_mulai->format('d F Y')??'-';
                $object->tanggalselesai = $key->status_finish === 1 ? $key->tanggal_selesai->format('d F Y'):'-';
                $object->jumlahpengunjung = $key->search_log_count;
                $object->url = config('app.FE_url').'project/'.$key->slug;
                $data[] = $object;
            }

            $out['data'] = $data;

            return response()->json([
                "status"    =>  1,
                "data"      =>  $out
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   'Something Went Wrong';
            return response()->json([
                "status"    =>  0,
                "data"      =>  $data
            ],200);
        }
    }

    public function vendortopfive(){
        try {
            // setting 12 bulan kebelakang
            $num= [];
            $label= [];
            $data=[];
            $vend = ConsultantLog::select('consultant_id', DB::raw('count(consultant_id) quantity'))->groupBy('consultant_id')->orderby('quantity','desc')->limit(5)->get();

            foreach ($vend as $key) {
                $object = new stdClass;
                $object->nama = $key->consultant->nama??'-';
                $object->url = isset($key->consultant->id) ? config('app.FE_url').'consultant/'.$key->consultant->id : config('app.FE_url').'consultant/'.'-';
                $object->tentang = $key->consultant->tentang??'-';
                $object->bidang = $key->consultant->bidang??'-';
                $object->website = $key->consultant->website??'-';
                $object->telepon = $key->consultant->telepon??'-';
                $object->email = $key->consultant->email??'-';
                $object->facebook = $key->consultant->facebook??'-';
                $object->instagram = $key->consultant->instagram??'-';
                $object->lokasi = $key->consultant->lokasi??'-';
                $object->jumlahpengunjung = $key->quantity??0;
                $object->Dibuat = $key->consultant->created_at->format('d F Y')??'-';
                $data[] = $object;
            }

            $out['data'] = $data;
            return response()->json([
                "status"    =>  1,
                "data"      =>  $out
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   'Something Went Wrong';
            return response()->json([
                "status"    =>  0,
                "data"      =>  $data
            ],200);
        }
    }

    public function getProjectVisitor()
    {
        $data=[];
        $today = Carbon::now();
        $last30d = date('Y-m-d', strtotime('-30 days'));
        $last24h = date('Y-m-d', strtotime('-24 hours'));

        $q1 = Search_log::whereBetween('created_at', [$last30d, $today])->get();
        $q2 = Search_log::whereBetween('created_at', [$last24h, $today])->get();
        $q3 = Search_log::get();
        // $query = Search_log::whereBetween('created_at', [$last30d, $today])->orderby('created_at','asc')->get();

        $query = DB::table('search_logs')
        ->join('projects', 'projects.id', '=', 'search_logs.project_id')
        ->where('flag_mcs', 5)
        ->whereBetween('search_logs.created_at', [$last30d, $today])
        ->orderby('search_logs.created_at','asc')
        ->get();

        // slicing
        $temp=[];
        foreach ($query as $item) {
            $date = Carbon::parse($item->created_at);
            $temp[] = $date->format('Y-m-d');
            // $temp[]  = $item->created_at->format('Y-m-d');
        }
        $coll   =   collect($temp)->countby();
        foreach ($coll as $key => $value) {
            $obj        =   new stdClass();
            $obj->date  =   $key;
            $obj->jumlahpengunjung =   $value;
            $data[]     =   $obj;
        }
        // return response()->json($data);
        $out['data'] = $data;
        try {
            // $data['message'] = 'Get Data Success';
            // $data['data'] = $query;
            // $data['last30'] = $q1;
            return response()->json([
                "status"    =>  1,
                "data"      =>  $out,
                "v_today"   =>  count($q1),
                "v_last30"  =>  count($q2),
                "v_total"   =>  count($q3)
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   'Something Went Wrong';
            return response()->json([
                "status"    =>  0,
                "data"      =>  $data
            ],200);
        }
    }

    public function getProjectKonsultan()
    {
        $data=[];

        $query = ConsultantProject::with(['consultant' => function($q){
            $q->with(['project' => function ($r){
                $r->where('flag_mcs', 5);
            }]);
        }])->select('consultant_id', DB::raw("count(consultant_id) as jumlah_project"))
        // }])->select('consultant_id', DB::raw("count(projects.id) as jumlah_project"))
        // }])->select('consultant_id', DB::raw("count(WHEN projects.flag_mcs = 5 THEN projects.id END) as jumlah_project"))
        // ->leftJoin('projects', 'consultant_projects.project_id', '=', 'projects.id')
        // ->where('projects.flag_mcs', 5)
        ->groupBy('consultant_id')->get();

        // return response()->json([$query]);

        $temp=  [];
        foreach ($query as $q) {
            $obj = new stdClass;
            // $obj->jumlah_project    = count($q->consultant->project)??0;
            if (count($q->consultant->project->where('flag_mcs', 5)) > 1) {
                $obj->jumlah_project    = count($q->consultant->project->where('flag_mcs', 5));
            }
            else if (count($q->consultant->project->where('flag_mcs', 5)) == 1){
                // return response()->json([count($q->consultant->project->where('flag_mcs', 5)) == 1]);
                $obj->jumlah_project    = 1;
            }
            else {
                $obj->jumlah_project    = 0;
                // return response()->json([count((array)$q->consultant->project->where('flag_mcs', 5))]);
            }
            // $obj->jumlah_project    = $q->consultant->project->count()??0;
            // $obj->jumlah_project    = $q->jumlah_project??0;
            // return response()->json([$q->consultant->project->where('flag_mcs', 5)]);
            // return response()->json([count((array)$q->consultant->project->where('flag_mcs', 5))]);
            // return response()->json([$obj->jumlah_project]);
            // $obj->jumlah_project    = $q->jumlah_project??0;
            $obj->nama_consultant   = $q->consultant->nama??'-'; //many to many
            $obj->tentang           = $q->consultant->tentang??'-';
            $obj->bidang            = $q->consultant->bidang??'-';
            $obj->website           = $q->consultant->website??'-';
            $obj->telepon           = $q->consultant->telepon??'-';
            $obj->email             = $q->consultant->email??'-';
            $obj->facebook          = $q->consultant->facebook??'-';
            $obj->instagram         = $q->consultant->instagram??'-';
            $obj->project           = "";
            if(isset($q->consultant->project)){
                foreach ($q->consultant->project->where('flag_mcs', 5) as $item) {
                    if ($obj->project === "") {
                            $obj->project          = $item->nama;
                    }else{
                            $obj->project          = $obj->project.", ".$item->nama;
                    }
                }
            }
            $obj->url               = isset($q->consultant->id) ? config('app.FE_url').'consultant/'.$q->consultant->id : config('app.FE_url').'dashboard/'.'#';
            $temp[]                 = $obj;
        }

        $a = collect($temp)->sortByDesc('jumlah_project');
        foreach ($a as $q) {
            if ($q->jumlah_project < 1) {
                $obj = new stdClass;
                $obj->jumlah_project  = $q->jumlah_project??0;
                $obj->nama_consultant = '-'; //many to many
                $obj->tentang         = '-';
                $obj->bidang          = '-';
                $obj->website         = '-';
                $obj->telepon         = '-';
                $obj->email           = '-';
                $obj->facebook        = '-';
                $obj->instagram       = '-';
                $obj->list_project    = '-';
                $obj->url             = '-'; //many to many
                $data[] = $obj;

            } else {
                $obj = new stdClass;
                $obj->jumlah_project  = $q->jumlah_project??0;
                $obj->nama_consultant = $q->nama_consultant??'-'; //many to many
                $obj->tentang         = $q->tentang??'-';
                $obj->bidang          = $q->bidang??'-';
                $obj->website         = $q->website??'-';
                $obj->telepon         = $q->telepon??'-';
                $obj->email           = $q->email??'-';
                $obj->facebook        = $q->facebook??'-';
                $obj->instagram       = $q->instagram??'-';
                $obj->list_project    = $q->project??'-';
                $obj->url             = $q->url??'-'; //many to many
                $data[] = $obj;
            }
        }

        $out['data'] = $data;
        try {
            $data['message'] = 'Get Data Success';
            $data['data'] = $query;
            return response()->json([
                "status"    =>  1,
                "data"      =>  $out,
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   'Something Went Wrong';
            return response()->json([
                "status"    =>  0,
                "data"      =>  $data
            ],200);
        }
    }

    public function getProjectDivisi()
    {
        try {
            $data=[];

            $query = Project::with(['divisi' => function($q){
                $q->with('project');
            }])->select('divisi_id', DB::raw("count(divisi_id) as count"))
            ->groupBy('divisi_id')
            ->where('flag_mcs', 5)
            ->get();
            // return $query;
            $temp   =   [];
            foreach ($query as $q) {
                $obj = new stdClass;
                $obj->jumlah_project = $q->count??0;
                $obj->nama_direktorat= $q->divisi->direktorat??'-';
                $obj->nama_divisi    = $q->divisi->divisi??'-';
                $obj->shortname      = $q->divisi->shortname??'-';
                $obj->project           = "";
                foreach ($q->divisi->project->where('flag_mcs', 5) as $item) {
                    if ($obj->project === "") {
                        $obj->project          = $item->nama;
                    }else{
                        $obj->project          = $obj->project.", ".$item->nama;
                    }
                }
                $temp[] = $obj;
            }
            $a = collect($temp)->sortByDesc('jumlah_project');
            foreach ($a as $q) {
                if ($q->jumlah_project < 1) {
                    $obj = new stdClass;
                    $obj->jumlah_project = $q->jumlah_project??0;
                    $obj->nama_direktorat= '-'; //many to many
                    $obj->nama_divisi    = '-';
                    $obj->shortname      = '-';
                    $obj->list_project   = '-';
                    $data[] = $obj;
                } else {
                    $obj = new stdClass;
                    $obj->jumlah_project = $q->jumlah_project??0;
                    $obj->nama_direktorat= $q->nama_direktorat??'-'; //many to many
                    $obj->nama_divisi    = $q->nama_divisi??'-';
                    $obj->shortname      = $q->shortname??'-';
                    $obj->list_project   = $q->project??'-';
                    $data[] = $obj;
                }
            }
            $out['data'] = $data;

            $data['message'] = 'Get Data Success';
            $data['data'] = $query;
            return response()->json([
                "status"    =>  1,
                "data"      =>  $out,
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   'Something Went Wrong';
            return response()->json([
                "status"    =>  0,
                "data"      =>  $data
            ],200);
        }
    }
}
