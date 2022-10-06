<?php

namespace App\Http\Controllers;

use App\AttachFile;
use App\CommunicationSupport;
use App\Consultant;
use App\Divisi;
use App\Implementation;
use App\Keywords;
use App\Project;
use App\Project_managers;
use App\TempUpload;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Validator;

class ManageComSupport extends Controller {

    public function getAllComInitiative(Request $request, $type) {
        try{
            $model = CommunicationSupport::with(['attach_file'])
                ->leftJoin('projects', 'projects.id', '=', 'communication_support.project_id')
                ->select(DB::raw("communication_support.*, projects.nama, projects.slug as slug_project"))
                ->where('communication_support.type_file', $type)
                ->where('communication_support.status', '!=', 'deleted');

            $limit = intval($request->get('limit', 10));
            $offset = intval($request->get('offset', 0));
            $order = 'asc';
            if($request->get('order')) {
                $order = $request->get('order');
            }
            if($request->get('sort')) {
                $model->orderBy('communication_support.created_at', $order);
            }
            if($request->get('search')) {
                $model->where('communication_support.title', 'like','%'.$request->get('search').'%');
            }

            $data = $model->skip($offset)->take($limit)->get();

            $count = count($data);
            $countTotal = $model->count();
            $countNotFilter = CommunicationSupport::with(['attach_file'])
                ->where('type_file', $type)->where('status', '!=', 'deleted')->count();

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

    public function getAllStrategic(Request $request) {
        try{
//            $model = CommunicationSupport::with(['attach_file'])->where('communication_support.type_file', $type)->where('communication_support.status', '!=', 'deleted');
            /*$model = Project::join('communication_support', 'projects.id', '=', 'communication_support.project_id')
                ->where('communication_support.status', '!=', 'deleted');*/
            $model = Project::with(['communication_support' => function($q) {
                $q->where('status', '!=', 'deleted');
            }])->whereIn('id', function ($query) {
                $query->select('project_id')->distinct('project_id')
                    ->from(with(new CommunicationSupport)->getTable())
                    ->where('status', '!=', 'deleted');
            });

            $limit = intval($request->get('limit', 10));
            $offset = intval($request->get('offset', 0));
            $order = 'asc';
            if($request->get('order')) {
                $order = $request->get('order');
            }
            if($request->get('sort')) {
                $model->orderBy('projects.created_at', $order);
            }
            if($request->get('search')) {
                $model->where('projects.nama', 'like','%'.$request->get('search').'%');
            }

            $data = $model->skip($offset)->take($limit)->get();

            $count = count($data);
            $countTotal = $model->count();
            $countNotFilter = Project::with(['communication_support' => function($q) {
                $q->where('status', '!=', 'deleted');
            }])->whereIn('id', function ($query) {
                $query->select('project_id')->distinct('project_id')
                    ->from(with(new CommunicationSupport)->getTable())
                    ->where('status', '!=', 'deleted');
            })->count();

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

    public function getStrategicByProject($slug) {
        try{
//            $model = CommunicationSupport::with(['attach_file'])->where('communication_support.type_file', $type)->where('communication_support.status', '!=', 'deleted');
            /*$model = Project::join('communication_support', 'projects.id', '=', 'communication_support.project_id')
                ->where('communication_support.status', '!=', 'deleted');*/
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
                ->where('status', '!=', 'deleted')->select('type_file')->distinct()->get();

            $type_file = [];
            foreach($types as $r){
                $key = array_search($r->type_file, array_column($type_list, 'id'));
                $datas = CommunicationSupport::where('project_id', $project->id)
                    ->where('status', '!=', 'deleted')->where('type_file', $r->type_file)->take(5)->get();
                $datas_total = CommunicationSupport::where('project_id', $project->id)
                    ->where('status', '!=', 'deleted')->where('type_file', $r->type_file)->count();

                $type_list[$key]['content'] = $datas;
                $type_list[$key]['total_content'] = $datas_total;
                $type_file[] = $type_list[$key];
            }
            $data['type'] = $type_file;

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
        try{
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
                ->leftJoin('projects', 'projects.id', '=', 'communication_support.project_id')
                ->select(DB::raw("communication_support.*, projects.nama, projects.slug as slug_project"))
                ->where('communication_support.type_file', $type)
                ->where('communication_support.project_id', $project->id)
                ->where('communication_support.status', '!=', 'deleted');

            $limit = intval($request->get('limit', 10));
            $offset = intval($request->get('offset', 0));
            $order = 'asc';
            if($request->get('order')) {
                $order = $request->get('order');
            }
            if($request->get('sort')) {
                $model->orderBy('communication_support.created_at', $order);
            }
            if($request->get('search')) {
                $model->where('communication_support.title', 'like','%'.$request->get('search').'%');
            }

            $data = $model->skip($offset)->take($limit)->get();

            $count = count($data);
            $countTotal = $model->count();
            $countNotFilter = CommunicationSupport::with(['attach_file'])
                ->where('type_file', $type)
                ->where('project_id', $project->id)
                ->where('status', '!=', 'deleted')->count();

            return response()->json([
                "message"   => "GET Berhasil",
                "status"    => 1,
                "data"      => $data,
                "project"   => $project,
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

    public function setStatusComInit($status, $id) {
        try {
            $sekarang = Carbon::now();

            if ($status == 'approve') {
                $updateDetails = [
                    'status' =>  $status,
                    'approve_at' =>  $sekarang,
                    'approve_by' =>  Auth::User()->personal_number,
                ];
                /*DB::select(" update communication_support set `approve_at` = '$sekarang',
                                     `approve_by` = '$user', `status` = '$status' where id = '$id' ");*/
            } else if ($status == 'publish') {
                $updateDetails = [
                    'status' =>  $status,
                    'publish_at' =>  $sekarang,
                    'publish_by' =>  Auth::User()->personal_number
                ];
            } else if ($status == 'reject') {
                $updateDetails = [
                    'status' =>  $status,
                    'reject_at' =>  $sekarang,
                    'reject_by' =>  Auth::User()->personal_number,
                ];
            } else if ($status == 'unpublish') {
                $updateDetails = [
                    'status' =>  $status,
                    'unpublish_at' =>  $sekarang,
                    'unpublish_by' =>  Auth::User()->personal_number,
                ];
            }

            $updateDetails['updated_by'] = Auth::User()->personal_number;
            CommunicationSupport::where('id', $id)
                ->update($updateDetails);

            $data['message']    =   ucwords($status).' Proyek Berhasil.';
            $data['toast']    =   ucwords($status) == 'Publish' ? 'Proyek berhasil diterbitkan' : ucwords($status).' Berhasil.';
            return response()->json([
                "status"    => 1,
                "data"      => $data,
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   ucwords($status).' Proyek Gagal';
            $data['toast']    =   ucwords($status) == 'Publish' ? 'Project gagal diterbitkan!' : ucwords($status).' Proyek Gagal.';
            return response()->json([
                'status'    =>  0,
                'data'      =>  $data
            ],200);
        }
    }

    public function deleteComInit($id) {
        try {
            $sekarang = Carbon::now();

            CommunicationSupport::where('id', $id)
                ->update(['status' => 'deleted', 'deleted_at' => $sekarang,
                    'deleted_by' => Auth::User()->personal_number, 'updated_by' => Auth::User()->personal_number]);

            $data['message']    =  'Delete Proyek Berhasil.';
            return response()->json([
                "status"    => 1,
                "data"      => $data,
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   'Delete Proyek Gagal';
            return response()->json([
                'status'    =>  0,
                'data'      =>  $data
            ],200);
        }
    }

    public function getAllImplementation(Request $request, $step) {
        try{
            $model = Implementation::leftJoin('projects', 'projects.id', '=', 'implementation.project_id')
                ->select(DB::raw("implementation.*, projects.nama, projects.slug as slug_project"))
                ->where('status', '!=', 'deleted');
            $modelCount = Implementation::where('status', '!=', 'deleted');

            $limit = intval($request->get('limit', 10));
            $offset = intval($request->get('offset', 0));
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
            $data = $model->skip($offset)->take($limit)->get();

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

    public function setStatusImplementation($status, $id) {
        try {
            $sekarang = Carbon::now();

            if ($status == 'approve') {
                $updateDetails = [
                    'status' =>  $status,
                    'approve_at' =>  $sekarang,
                    'approve_by' =>  Auth::User()->personal_number,
                ];
                /*DB::select(" update communication_support set `approve_at` = '$sekarang',
                                     `approve_by` = '$user', `status` = '$status' where id = '$id' ");*/
            } else if ($status == 'publish') {
                $updateDetails = [
                    'status' =>  $status,
                    'publish_at' =>  $sekarang,
                    'publish_by' =>  Auth::User()->personal_number
                ];
            } else if ($status == 'reject') {
                $updateDetails = [
                    'status' =>  $status,
                    'reject_at' =>  $sekarang,
                    'reject_by' =>  Auth::User()->personal_number,
                ];
            } else if ($status == 'unpublish') {
                $updateDetails = [
                    'status' =>  $status,
                    'unpublish_at' =>  $sekarang,
                    'unpublish_by' =>  Auth::User()->personal_number,
                ];
            }

            $updateDetails['updated_by'] = Auth::User()->personal_number;
            Implementation::where('id', $id)
                ->update($updateDetails);

            $data['message']    =   ucwords($status).' Proyek Berhasil.';
            $data['toast']    =   ucwords($status) == 'Publish' ? 'Proyek berhasil diterbitkan' : ucwords($status).' Berhasil.';
            return response()->json([
                "status"    => 1,
                "data"      => $data,
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   ucwords($status).' Proyek Gagal';
            $data['toast']    =   ucwords($status) == 'Publish' ? 'Project gagal diterbitkan!' : ucwords($status).' Proyek Gagal.';
            return response()->json([
                'status'    =>  0,
                'data'      =>  $data
            ],200);
        }
    }

    public function deleteImplementation($id) {
        try {
            $sekarang = Carbon::now();

            Implementation::where('id', $id)
                ->update(['status' => 'deleted', 'deleted_at' => $sekarang,
                    'deleted_by' => Auth::User()->personal_number, 'updated_by' => Auth::User()->personal_number]);

            $data['message']    =  'Delete Proyek Berhasil.';
            return response()->json([
                "status"    => 1,
                "data"      => $data,
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   'Delete Proyek Gagal';
            return response()->json([
                'status'    =>  0,
                'data'      =>  $data
            ],200);
        }
    }

    public function form_content($slug = "*") {
        $type_file = (object) array(
            array("value" => "article", "name" => "Article"),
            array("value" => "logo", "name" => "Icon, Logo, Maskot BRIVO"),
            array("value" => "infographics", "name" => "Infographics"),
            array("value" => "transformation", "name" => "Transformation Journey"),
            array("value" => "podcast", "name" => "Podcast"),
            array("value" => "video", "name" => "Video Content"),
            array("value" => "instagram", "name" => "Instagram Content"),
        );

        try {
            $data   = [];

            $project = Project::get();

            if ($slug == "*") {
                $datas     =   [];
            } else {
                $datas = CommunicationSupport::with(['attach_file' => function($q) {
                    $q->where('tipe', 'content');
                }])->leftJoin('projects', 'projects.id', '=', 'communication_support.project_id')
                    ->where('communication_support.slug', $slug)
                    ->select(DB::raw("communication_support.*, projects.nama"))->first();
            }

            $querydirektorat  = Divisi::select('direktorat')->groupBy('direktorat')->get();
            $query            = Divisi::get();

            $data['project'] = $project;
            $data['direktorat'] = $querydirektorat;
            $data['divisi'] = $query;
            $data['type_file'] = $type_file;
            $data['data'] = $datas;

            return response()->json([
                "status"    => '1',
                "data"      => $data
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   'Something Went Wrong';
            return response()->json([
                "status"    =>  0,
                "data"      =>  $data
            ],200);
        }
    }

    public function form_implementation($slug = "*") {
        try {
            $data   = [];

            if ($slug == "*") {
                $datas     =   [];
            } else {
                $datas = Implementation::leftJoin('projects', 'projects.id', '=', 'implementation.project_id')
                    ->where('implementation.slug', $slug)
                    ->select(DB::raw("implementation.*, projects.nama"))->first();
                if ($datas->is_restricted == 1) {
                    $user_access = explode(",",$datas->user_access);
                    $access = [];
                    foreach($user_access as $u){
                        $user = User::where('personal_number', $u)->first();
                        $access[] = $user;
                    }
                    $datas['user'] = $access;
                }
            }

            $querydirektorat  = Divisi::select('direktorat')->groupBy('direktorat')->get();
            $query            = Divisi::get();
            $tags             = keywords::distinct()->get(['nama']);
            $consultant       = Consultant::get();
            // $checker= User::where('divisi',Auth::User()->divisi)->where('role','1')->get();
            // $signer = User::where('divisi',Auth::User()->divisi)->where('role','2')->get();

            $data['direktorat'] = $querydirektorat;
            $data['divisi'] = $query;
            $data['tags'] = $tags;
            $data['consultant'] = $consultant;
            $data['data'] = $datas;
            // $data['checker'] = $checker;
            // $data['signer'] = $signer;

            return response()->json([
                "status"    => '1',
                "data"      => $data
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   'Something Went Wrong';
            return response()->json([
                "status"    =>  0,
                "data"      =>  $data
            ],200);
        }
    }

    public function createContent($id = "*") {
        $validator = Validator::make(request()->all(), [
            'thumbnail'         => "required",
            'title'             => "required",
            'file_type'         => 'required',
            'deskripsi'         => 'required',
            'attach'            => 'required',
            'tgl_mulai'     => 'required',
        ]);

        // handle jika tidak terpenuhi
        if ($validator->fails()) {
            $data_error['message'] = $validator->errors();
            $data_error['error_code'] = 1; //error
            return response()->json([
                'status' => 0,
                'data'  => $data_error
            ], 400);
        }

        // date
        date_default_timezone_set('Asia/Jakarta');
        $now = Carbon::now();
        $waktu = $now->year."".$now->month."".$now->day."".$now->hour."".$now->minute."".$now->second;

        try {
            if ($id == "*") {
                $create = CommunicationSupport::create([
                    'project_id' => request()->project_id,
                    'divisi_id' => request()->divisi,
                    'title' => request()->title,
                    'slug' => $waktu."-".\Str::slug(request()->title),
                    'type_file' => request()->file_type,
                    'desc' => request()->deskripsi,
                    'status' => 'unpublish',
                    'tanggal_mulai'         => request()->tgl_mulai,
                    'tanggal_selesai'       => request()->tgl_selesai,
                    'thumbnail' => request()->thumbnail,
                    'user_maker' => Auth::User()->personal_number,
                ]);

                // attach
                $tampung_attach     =   request()->attach;
                if (isset($tampung_attach)) {
                    for ($i=0; $i < count($tampung_attach) ; $i++) {
                        $cek = TempUpload::where('path',$tampung_attach[$i])->first();
                        if($cek){
                            $attach['com_id']       = $create->id;
                            $attach['tipe']         = 'content';
                            $attach['nama']         = $cek->nama_file;
                            $attach['jenis_file']   = $cek->type;
                            $attach['url_file']     = $cek->path;
                            $attach['size']         = $cek->size;
                            AttachFile::create($attach);

                            $cek->delete();
                        }
                    }
                }
            } else {
                $upd    =   CommunicationSupport::where('id',$id)->first();
                if (!$upd) {
                    $data_error['message'] = 'Project Not Found';
                    $data_error['error_code'] = 1; //error
                    return response()->json([
                        'status' => 0,
                        'data'  => $data_error
                    ], 400);
                }

                $data_new['project_id']     = request()->project_id;
                $data_new['divisi_id']           = request()->divisi;
                $data_new['title']          = request()->title;
                $data_new['slug']           = $waktu."-".\Str::slug(request()->title);
                $data_new['desc']           = request()->deskripsi;
                $data_new['tanggal_mulai']           = request()->tgl_mulai;
                $data_new['tanggal_selesai']           = request()->tgl_selesai;
                $data_new['thumbnail']      = request()->thumbnail;
                $data_new['updated_by']     = Auth::User()->personal_number;

                $upd->update($data_new);

                $tampung_attach     =   request()->attach;
                if (isset($tampung_attach)) {
                    AttachFile::whereNotIn('url_file', $tampung_attach)->where('com_id',$id)->delete();
                }

                if (isset($tampung_attach)) {
                    for ($i=0; $i < count($tampung_attach) ; $i++) {
                        $cek = TempUpload::where('path',$tampung_attach[$i])->first();
                        if($cek){
                            $attach['com_id']       = $id;
                            $attach['tipe']         = "content";
                            $attach['nama']         = $cek->nama_file;
                            $attach['jenis_file']   = $cek->type;
                            $attach['url_file']     = $cek->path;
                            $attach['size']         = $cek->size;
                            AttachFile::create($attach);

                            $cek->delete();
                        }
                        /*if($cek){
                            AttachFile::updateOrCreate([
                                'url_file' => $cek->path
                            ],
                                [
                                    'com_id' => $id,
                                    'tipe' => $upd->type_file,
                                    'nama' => $cek->nama_file,
                                    'jenis_file' => $cek->type,
                                    'size' => $cek->size,
                                ]);

                            $cek->delete();
                        }*/
                    }
                }
            }

            $temp = TempUpload::where('path',request()->thumbnail)->first();
            if ($temp) {
                $temp->delete();
            }

            $data['message']    =   'Save Data Berhasil';
            return response()->json([
                "status"    =>  1,
                "data"      => $data
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   'Save Data Gagal, Mohon Coba Lagi';
            $data['error_code'] = 0; //error
            return response()->json([
                'status'    =>  0,
                'data'      =>  $data,
                'error'     =>  $th
            ],200);
        }
    }

    public function createImplementation($id = "*") {
        $validator = Validator::make(request()->all(), [
            'thumbnail'     => "required",
            'direktorat'    => "required",
            'divisi'        => 'required',
            'nama_project'  => 'required',
            'status'        => 'required',
            'tgl_mulai'     => 'required',
            'pm'            => 'required',
            'emailpm'       => 'required',
            'restricted'    => 'required',
            'piloting'      => 'required',
            'rollout'       => 'required',
            'sosialisasi'   => 'required',
            'user'          => 'required',
            'checker'       => 'required',
            'signer'        => 'required',
            'token_bri'     => "required",
        ]);

        // handle jika tidak terpenuhi
        if ($validator->fails()) {
            $data_error['message'] = $validator->errors();
            $data_error['error_code'] = 1; //error
            return response()->json([
                'status' => 0,
                'data'  => $data_error
            ], 400);
        }

        // date
        date_default_timezone_set('Asia/Jakarta');
        $now = Carbon::now();
        $waktu = $now->year."".$now->month."".$now->day."".$now->hour."".$now->minute."".$now->second;

        try {
            $cekChecker =   User::where('personal_number',(int)request()->checker)->first();
            if (isset($cekChecker->personal_number)) {
                $dataChecker = $cekChecker;
            }else{
                $cheking = $this->getDataUser(request()->token_bri,(int)request()->checker);
                if($cheking == false){
                    $data['message']    =   'Get Data Checker Failed';
                    $data['error_code'] = 0; //error
                    return response()->json([
                        'status'    =>  0,
                        'data'      =>  $data
                    ],200);
                }else{
                    $dataChecker    =   $cheking;
                }
            }

            // cek akun signer
            $cekSigner =   User::where('personal_number',(int)request()->signer)->first();
            if (isset($cekSigner->personal_number)) {
                $dataSigner = $cekSigner;
            }else{
                $cheking = $this->getDataUser(request()->token_bri,(int)request()->signer);
                if($cheking == false){
                    $data['message']    =   'Get Data Signer Failed';
                    $data['error_code'] = 0; //error
                    return response()->json([
                        'status'    =>  0,
                        'data'      =>  $data
                    ],200);
                }else{
                    $dataSigner    =   $cheking;
                }
            }

            $create_pm    =   Project_managers::where('nama',request()->pm)->where('email',request()->emailpm)->first();
            if (!$create_pm) {
                $create_pm   = Project_managers::create([
                    'nama'      =>  request()->pm,
                    'email'     =>  request()->emailpm,
                ]);
            }

            // restricted
            if (request()->restricted == null || request()->restricted == "0") {
                $restricted = '0';
            }else{
                $restricted = '1';
            }
            if ($id == "*") {
                $create = Implementation::create([
                    'title' => request()->nama_project,
                    'slug' => $waktu."-".\Str::slug(request()->nama_project),
                    'divisi_id' => request()->divisi,
                    'project_managers_id'   => $create_pm->id,
                    'status' => 'unpublish',
                    'thumbnail' => request()->thumbnail,
                    'tanggal_mulai'         => request()->tgl_mulai,
                    'tanggal_selesai'       => request()->tgl_selesai,
                    'user_access'         => request()->user,
                    'desc_piloting'         => request()->deskripsi_pilot,
                    'desc_roll_out'         => request()->deskripsi_rollout,
                    'desc_sosialisasi'         => request()->deskripsi_sosialisasi,
                    'project_id'         => request()->project_id,
                    'user_checker'          => $dataChecker->personal_number,
                    'user_signer'           => $dataSigner->personal_number,
                    'user_maker'            => Auth::User()->personal_number,
                ]);

                // attach
                if (request()->piloting == 1) {
                    $tampung_attach_pilot     =   request()->attach_pilot;
                    if (isset($tampung_attach_pilot)) {
                        for ($i=0; $i < count($tampung_attach_pilot) ; $i++) {
                            $cek = TempUpload::where('path',$tampung_attach_pilot[$i])->first();
                            if($cek){
                                $attach['implementation_id']       = $create->id;
                                $attach['tipe']                    = 'piloting';
                                $attach['nama']                    = $cek->nama_file;
                                $attach['jenis_file']              = $cek->type;
                                $attach['url_file']                = $cek->path;
                                $attach['size']                    = $cek->size;
                                AttachFile::create($attach);

                                $cek->delete();
                            }
                        }
                    }
                }

                if (request()->rollout == 1) {
                    $tampung_attach_rollout     =   request()->attach_rollout;
                    if (isset($tampung_attach_rollout)) {
                        for ($i=0; $i < count($tampung_attach_rollout) ; $i++) {
                            $cek = TempUpload::where('path',$tampung_attach_rollout[$i])->first();
                            if($cek){
                                $attach['implementation_id']       = $create->id;
                                $attach['tipe']                    = 'rollout';
                                $attach['nama']                    = $cek->nama_file;
                                $attach['jenis_file']              = $cek->type;
                                $attach['url_file']                = $cek->path;
                                $attach['size']                    = $cek->size;
                                AttachFile::create($attach);

                                $cek->delete();
                            }
                        }
                    }
                }

                if (request()->sosialisasi == 1) {
                    $tampung_attach_sosialisasi     =   request()->attach_sosialisasi;
                    if (isset($tampung_attach_sosialisasi)) {
                        for ($i=0; $i < count($tampung_attach_sosialisasi) ; $i++) {
                            $cek = TempUpload::where('path',$tampung_attach_sosialisasi[$i])->first();
                            if($cek){
                                $attach['implementation_id']       = $create->id;
                                $attach['tipe']                    = 'sosialisasi';
                                $attach['nama']                    = $cek->nama_file;
                                $attach['jenis_file']              = $cek->type;
                                $attach['url_file']                = $cek->path;
                                $attach['size']                    = $cek->size;
                                AttachFile::create($attach);

                                $cek->delete();
                            }
                        }
                    }
                }
            } else {
                $cek_pm    =   Project_managers::where('nama',request()->pm)->where('email',request()->emailpm)->first();
                if (!$cek_pm) {
                    $cek_pm   = Project_managers::create([
                        'nama'      =>  request()->pm,
                        'email'     =>  request()->emailpm,
                    ]);
                }
                $upd    =   Implementation::where('id',$id)->first();
                if (!$upd) {
                    $data_error['message'] = 'Implementation Not Found';
                    $data_error['error_code'] = 1; //error
                    return response()->json([
                        'status' => 0,
                        'data'  => $data_error
                    ], 400);
                }

                $data_new['title']          = request()->nama_project;
                $data_new['slug']           = $waktu."-".\Str::slug(request()->nama_project);
                $data_new['divisi_id']           = request()->divisi;
                $data_new['project_managers_id']      = $cek_pm->id;
                $data_new['thumbnail']           = request()->thumbnail;
                $data_new['tanggal_mulai']           = request()->tgl_mulai;
                $data_new['tanggal_selesai']           = request()->tgl_selesai;
                $data_new['user_access']           = request()->user;
                $data_new['desc_piloting']           = request()->deskripsi_pilot;
                $data_new['desc_roll_out']           = request()->deskripsi_rollout;
                $data_new['desc_sosialisasi']           = request()->deskripsi_sosialisasi;
                $data_new['project_id']     = request()->project_id;
                $data_new['user_checker']     = $dataChecker->personal_number;
                $data_new['user_signer']     = $dataSigner->personal_number;
                $data_new['updated_by']     = Auth::User()->personal_number;

                $upd->update($data_new);

                if (request()->piloting == 1) {
                    $tampung_attach_pilot = request()->attach_pilot;
                    if (isset($tampung_attach_pilot)) {
                        AttachFile::whereNotIn('url_file', $tampung_attach_pilot)->where('implementation_id', $id)->delete();
                    }

                    if (isset($tampung_attach_pilot)) {
                        for ($i=0; $i < count($tampung_attach_pilot) ; $i++) {
                            $cek = TempUpload::where('path',$tampung_attach_pilot[$i])->first();
                            if($cek){
                                $attach['implementation_id']       = $id;
                                $attach['tipe']                    = 'piloting';
                                $attach['nama']                    = $cek->nama_file;
                                $attach['jenis_file']              = $cek->type;
                                $attach['url_file']                = $cek->path;
                                $attach['size']                    = $cek->size;
                                AttachFile::create($attach);

                                $cek->delete();
                            }
                        }
                    }
                }

                if (request()->rollout == 1) {
                    $tampung_attach_rollout     =   request()->attach_rollout;
                    if (isset($tampung_attach_rollout)) {
                        AttachFile::whereNotIn('url_file', $tampung_attach_rollout)->where('implementation_id',$id)->delete();
                    }
                    if (isset($tampung_attach_rollout)) {
                        for ($i=0; $i < count($tampung_attach_rollout) ; $i++) {
                            $cek = TempUpload::where('path',$tampung_attach_rollout[$i])->first();
                            if($cek){
                                $attach['implementation_id']       = $id;
                                $attach['tipe']                    = 'rollout';
                                $attach['nama']                    = $cek->nama_file;
                                $attach['jenis_file']              = $cek->type;
                                $attach['url_file']                = $cek->path;
                                $attach['size']                    = $cek->size;
                                AttachFile::create($attach);

                                $cek->delete();
                            }
                        }
                    }
                }

                if (request()->sosialisasi == 1) {
                    $tampung_attach_sosialisasi     =   request()->attach_sosialisasi;
                    if (isset($tampung_attach_sosialisasi)) {
                        AttachFile::whereNotIn('url_file', $tampung_attach_sosialisasi)->where('implementation_id',$id)->delete();
                    }
                    if (isset($tampung_attach_sosialisasi)) {
                        for ($i=0; $i < count($tampung_attach_sosialisasi) ; $i++) {
                            $cek = TempUpload::where('path',$tampung_attach_sosialisasi[$i])->first();
                            if($cek){
                                $attach['implementation_id']       = $id;
                                $attach['tipe']                    = 'sosialisasi';
                                $attach['nama']                    = $cek->nama_file;
                                $attach['jenis_file']              = $cek->type;
                                $attach['url_file']                = $cek->path;
                                $attach['size']                    = $cek->size;
                                AttachFile::create($attach);

                                $cek->delete();
                            }
                        }
                    }
                }
            }

            $temp = TempUpload::where('path',request()->thumbnail)->first();
            if ($temp) {
                $temp->delete();
            }

            $data['message']    =   'Save Data Berhasil';
            return response()->json([
                "status"    =>  1,
                "data"      => $data
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   'Save Data Gagal, Mohon Coba Lagi';
            $data['error_code'] = 0; //error
            return response()->json([
                'status'    =>  0,
                'data'      =>  $data,
                'error'     =>  $th
            ],200);
        }
    }

    public function getPublishComInnitiave(Request $request, $type) {
        try{
            $model = (new CommunicationSupport)->newQuery();

            $limit = intval($request->get('limit', 10));
            $offset = intval($request->get('offset', 0));
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

            $data = $model->where('type_file', $type)
                ->where('status', '=', 'publish')->skip($offset)->take($limit)->get();

            $count = count($data);
            $countTotal = CommunicationSupport::where('type_file', $type)->where('status', '=', 'publish')->count();

            return response()->json([
                "message"   => "GET Berhasil",
                "status"    => 1,
                "data"      => $data,
                "total"     => $count,
                "totalData" => $countTotal
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

    public function getDataUser($token_bri, $pn){
        try{
            $access_token   =   $token_bri;
            $ch2 = curl_init();
            $headers  = [
                'Content-Type: application/json',
                'Accept: application/json',
                "Authorization: Bearer $access_token",
            ];
            $postData = [
                'pernr'     => $pn,
            ];
            curl_setopt($ch2, CURLOPT_URL,config('app.api_detail_pekerja_bristar'));
            curl_setopt($ch2, CURLOPT_POST, 1);
            curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER , false);
            curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
            $result2     = curl_exec ($ch2);
            $hasil2      = json_decode($result2);
            if (isset($hasil2->responseData->COST_CENTER)) {
                // save data
                $ResponseCostcenter = $hasil2->responseData->COST_CENTER;
                $Responsenama       = $hasil2->responseData->NAMA;

                // cek
                if ($ResponseCostcenter <> null) {
                    $dataDivisi = Divisi::where('cost_center', $ResponseCostcenter)->first();
                    // add user
                    $new                    = new User();
                    $new->name              = $Responsenama??'User';
                    $new->personal_number   = $pn;
                    $new->username          = $Responsenama??'User';
                    $new->email             = str_replace(" ",".",$Responsenama)."@gmail.com";
                    $new->role              = 3;
                    $new->divisi            = isset($dataDivisi->id)? $dataDivisi->id : 11111111;
                    $new->last_login_at     = Carbon::now();
                    $new->xp                = 0;
                    $new->avatar_id         = 1;
                    $new->save();

                    // create Admin success
                    return $new;
                }
            }
        }catch(\Throwable $th){
            return false;
        }
    }
}
