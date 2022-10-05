<?php

namespace App\Http\Controllers;

use App\CommunicationSupport;
use App\Divisi;
use App\Implementation;
use App\Project;
use Illuminate\Http\Request;
use Auth;

class CommunicationSupportController extends Controller {

    public function getCommunicationInitiative(Request $request, $type) {
        try {
            $model = CommunicationSupport::with(['attach_file'])->where('type_file', $type)
                ->where('status',  'publish');

            $order = 'asc';
            if($request->get('order')) {
                $order = $request->get('order');
            }
            if($request->get('sort')) {
                $model->orderBy($request->get('sort'), $order);
            }
            if($request->get('search')) {
                $model->where('title', 'like','%'.$request->get('search').'%');
            }
            if($request->get('year')) {
                $model->whereYear('created_at', $request->get('year'));
            }
            if($request->get('month')) {
                $model->whereMonth('created_at', $request->get('month'));
            }
            if($request->get('divisi')) {
                $where_in = explode(",",$request->get('divisi'));
                $model->whereIn('divisi_id', $where_in);
            }

            $data = $model->paginate(6);

            $count = count($data);
            $countTotal = $model->count();
            $countNotFilter = CommunicationSupport::with(['attach_file'])
                ->where('type_file', $type)->where('status',  'publish')->count();

            return response()->json([
                "message"   => "GET Berhasil",
                "status"    => 1,
                "data"      => $data,
                "totalRow"  => $count,
                "total"     => $countTotal,
                "totalData" => $countNotFilter
            ],200);
        } catch (\Throwable $th){
            $datas['message']    =   'GET Gagal';
            return response()->json([
                'status'    =>  0,
                'data'      =>  $datas,
                'error' => $th
            ],200);
        }

    }

    public function getStrategic(Request $request) {
        try {
            $model = Project::with(['communication_support' => function($q) {
                $q->where('status', 'publish');
            }]);

            $order = 'asc';
            if($request->get('order')) {
                $order = $request->get('order');
            }
            if($request->get('sort')) {
                $model->orderBy($request->get('sort'), $order);
            }
            if($request->get('search')) {
                $model->where('projects.nama', 'like','%'.$request->get('search').'%');
            }
            if($request->get('year')) {
                $model->whereYear('created_at', $request->get('year'));
            }
            if($request->get('month')) {
                $model->whereMonth('created_at', $request->get('month'));
            }
            if($request->get('divisi')) {
                $where_in = explode(",",$request->get('divisi'));
//                $model->whereIn('divisi_id', $where_in);
                $model->whereIn('id', function ($query) use ($where_in) {
                    $query->select('project_id')->distinct('project_id')
                        ->from(with(new CommunicationSupport)->getTable())
                        ->where('status', 'publish')
                        ->whereIn('divisi_id', $where_in);
                });
            } else {
                $model->whereIn('id', function ($query) {
                    $query->select('project_id')->distinct('project_id')
                        ->from(with(new CommunicationSupport)->getTable())
                        ->where('status', 'publish');
                });
            }

            $data = $model->get();

            $count = count($data);
            $countNotFilter = Project::with(['communication_support' => function($q) {
                $q->where('status', 'publish');
            }])->whereIn('id', function ($query) {
                $query->select('project_id')->distinct('project_id')
                    ->from(with(new CommunicationSupport)->getTable())
                    ->where('status', 'publish');
            })->count();

            return response()->json([
                "message"   => "GET Berhasil",
                "status"    => 1,
                "data"      => $data,
                "total"     => $count,
                "totalData" => $countNotFilter
            ],200);
        } catch (\Throwable $th){
            $datas['message']    =   'GET Gagal';
            return response()->json([
                'status'    =>  0,
                'data'      =>  $datas,
                'error' => $th
            ],200);
        }
    }

    public function getStrategicByProject(Request $request, $slug) {
        try {
            $type_list = [
                ["id" => "article", "name" => "Articles"],
                ["id" => "logo", "name" => "Icon, Logo, Maskot BRIVO"],
                ["id" => "infographics", "name" => "Infographics"],
                ["id" => "transformation", "name" => "Transformation Journey"],
                ["id" => "podcast", "name" => "Podcast"],
                ["id" => "video", "name" => "Video Content"],
                ["id" => "instagram", "name" => "Instagram Content"],
            ];
            $project        = Project::where('slug',$slug)->first();
            if (!$project) {
                $data_error['message'] = 'Proyek tidak ditemukan!';
                $data_error['error_code'] = 1; //error
                return response()->json([
                    'status' => 0,
                    'data'  => $data_error
                ], 400);
            }
            $data['project'] = $project;
            $types = CommunicationSupport::where('project_id', $project->id)
                ->where('status', 'publish')->select('type_file')->distinct()->get();

            $model = CommunicationSupport::where('project_id', $project->id)
                ->where('status', 'publish');
            $order = 'asc';
            if($request->get('order')) {
                $order = $request->get('order');
            }
            if($request->get('sort')) {
                $model->orderBy($request->get('sort'), $order);
            }
            if($request->get('search')) {
                $model->where('title', 'like','%'.$request->get('search').'%');
            }
            if($request->get('year')) {
                $model->whereYear('created_at', $request->get('year'));
            }
            if($request->get('month')) {
                $model->whereMonth('created_at', $request->get('month'));
            }
            if($request->get('divisi')) {
                $where_in = explode(",",$request->get('divisi'));
                $model->whereIn('divisi_id', $where_in);
            }

            $type_file = [];
            foreach($types as $r){
                $key = array_search($r->type_file, array_column($type_list, 'id'));
                $datas = $model->where('type_file', $r->type_file)->take(5)->get();
                $datas_total = $model->where('type_file', $r->type_file)->count();
                $total = CommunicationSupport::where('project_id', $project->id)
                    ->where('status', '!=', 'deleted')->where('type_file', $r->type_file)->count();

                $type_list[$key]['data'] = $datas;
                $type_list[$key]['total_data'] = $datas_total;
                $type_list[$key]['total_notFiltered'] = $total;
                $type_file[] = $type_list[$key];
            }
            $data['content'] = $type_file;

            return response()->json([
                "message"   => "GET Berhasil",
                "status"    => 1,
                "data"      => $data,
            ],200);
        } catch (\Throwable $th){
            $datas['message']    =   'GET Gagal';
            return response()->json([
                'status'    =>  0,
                'data'      =>  $datas,
                'error' => $th
            ],200);
        }
    }

    public function getStrategicByProjectAndType(Request $request, $slug, $type) {
        try {
            $project        = Project::where('slug',$slug)->first();
            if (!$project) {
                $data_error['message'] = 'Proyek tidak ditemukan!';
                $data_error['error_code'] = 1; //error
                return response()->json([
                    'status' => 0,
                    'data'  => $data_error
                ], 400);
            }
            $model = CommunicationSupport::with(['attach_file'])
                ->where('communication_support.type_file', $type)
                ->where('project_id', $project->id)
                ->where('communication_support.status', 'publish');

            $order = 'asc';
            if($request->get('order')) {
                $order = $request->get('order');
            }
            if($request->get('sort')) {
                $model->orderBy($request->get('sort'), $order);
            }
            if($request->get('search')) {
                $model->where('title', 'like','%'.$request->get('search').'%');
            }
            if($request->get('year')) {
                $model->whereYear('created_at', $request->get('year'));
            }
            if($request->get('month')) {
                $model->whereMonth('created_at', $request->get('month'));
            }
            if($request->get('divisi')) {
                $where_in = explode(",",$request->get('divisi'));
                $model->whereIn('divisi_id', $where_in);
            }

            $data = $model->get();

            $count = count($data);
            $countNotFilter = CommunicationSupport::with(['attach_file'])
                ->where('communication_support.type_file', $type)
                ->where('project_id', $project->id)
                ->where('communication_support.status', 'publish')->count();

            return response()->json([
                "message"   => "GET Berhasil",
                "status"    => 1,
                "data"      => $data,
                "total"     => $count,
                "totalData" => $countNotFilter
            ],200);
        } catch (\Throwable $th){
            $datas['message']    =   'GET Gagal';
            return response()->json([
                'status'    =>  0,
                'data'      =>  $datas,
                'error' => $th
            ],200);
        }
    }

    public function getAllImplementation(Request $request, $step) {
        try {
            $model = Implementation::where('status', 'publish');
            $modelCount = Implementation::where('status', 'publish');

            if ($step == 'piloting') {
                $model->whereNotNull('desc_piloting');
                $modelCount->whereNotNull('desc_piloting');
            } else if ($step == 'roll-out') {
                $model->whereNotNull('desc_roll_out');
                $modelCount->whereNotNull('desc_roll_out');
            } else if ($step == 'sosialisasi') {
                $model->whereNotNull('desc_sosialisasi');
                $modelCount->whereNotNull('desc_sosialisasi');
            } else {
                $datas['message']    =   'GET Gagal';
                return response()->json([
                    'status'    =>  0,
                    'data'      =>  $datas,
                    'message'   => 'Tahap Implementasi tidak ditemukan'
                ],200);
            }

            $order = 'asc';
            if($request->get('order')) {
                $order = $request->get('order');
            }
            if($request->get('sort')) {
                $model->orderBy('created_at', $order);
            }
            if($request->get('search')) {
                $model->where('title', 'like','%'.$request->get('search').'%');
            }
            if($request->get('year')) {
                $model->whereYear('created_at', $request->get('year'));
            }
            if($request->get('month')) {
                $model->whereMonth('created_at', $request->get('month'));
            }
            if($request->get('divisi')) {
                $where_in = explode(",",$request->get('divisi'));
                $model->whereIn('divisi_id', $where_in);
            }

            $data = $model->paginate(5);

            $count = count($data);
            $countTotal = $model->count();
            $countNotFilter = $modelCount->count();

            return response()->json([
                "message"   => "GET Berhasil",
                "status"    => 1,
                "data"      => $data,
                "totalRow"  => $count,
                "total"     => $countTotal,
                "totalData" => $countNotFilter
            ],200);

        } catch (\Throwable $th){
            $datas['message']    =   'GET Gagal';
            return response()->json([
                'status'    =>  0,
                'data'      =>  $datas,
                'error' => $th
            ],200);
        }
    }

    public function getOneImplementation($slug) {
        try {
            $data = Implementation::with(['attach_file'])->where('status', 'publish')->where('slug', $slug)->first();

            $is_allowed = 0; //dilarang
            if ($data->is_restricted == 1) {
                $user_access = explode(",",$data->user_access);
                foreach($user_access as $user){
                    if($user == Auth::user()->personal_number) { //ketika yg di allow match dengan yg lagi login maka..
                        $is_allowed = 1; //diizinkan
                        break;
                    }
                }
            } else { //jika tidak bersifat RESTRICTED maka siapa saja yg akses dengan user apapun bisa lolos liat
                $is_allowed = 1;
            }
            $data['is_allowed']     =   $is_allowed??0;

            if (!$data) {
                $data_error['message'] = 'Implementation tidak ditemukan!';
                $data_error['error_code'] = 1; //error
                return response()->json([
                    'status' => 0,
                    'data'  => $data_error
                ], 400);
            }

            return response()->json([
                "message"   => "GET Berhasil",
                "status"    => 1,
                "data"      => $data,
            ],200);
        } catch (\Throwable $th){
            $datas['message']    =   'GET Gagal';
            return response()->json([
                'status'    =>  0,
                'data'      =>  $datas,
                'error' => $th
            ],200);
        }
    }

}
