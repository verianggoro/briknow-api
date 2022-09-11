<?php

namespace App\Http\Controllers;

use App\CommunicationInitiative;
use App\Implementation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManageComSupport extends Controller {

    public function getAllComInitiative(Request $request, $type) {
        try{
            $model = (new CommunicationInitiative)->newQuery();

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

            $data = $model->where('type_file', $type)->where('status', '!=', 'deleted')->skip($offset)->take($limit)->get();

            $count = count($data);
            $countTotal = CommunicationInitiative::where('type_file', $type)->where('status', '!=', 'deleted')->count();

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

    public function setStatusComInit($status, $id) {
        try {
            $sekarang = Carbon::now();

            if ($status == 'approve') {
                $updateDetails = [
                    'status' =>  $status,
                    'approve_at' =>  $sekarang,
                    'approve_by' =>  Auth::User()->personal_number,
                ];
                /*DB::select(" update communication_initiative set `approve_at` = '$sekarang',
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
            CommunicationInitiative::where('id', $id)
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

            CommunicationInitiative::where('id', $id)
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
            $model = (new Implementation)->newQuery();
            $modelCount = (new Implementation)->newQuery();

            $limit = intval($request->get('limit', 10));
            $offset = intval($request->get('offset', 0));
            $order = 'asc';
            if($request->get('order')) {
                $order = $request->get('order');
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
            $data = $model->where('status', '!=', 'deleted')->orderBy('tanggal_mulai', $order)->skip($offset)->take($limit)->get();

            $count = count($data);
            $countTotal = $modelCount->where('status', '!=', 'deleted')->count();

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

    public function setStatusImplementation($status, $id) {
        try {
            $sekarang = Carbon::now();

            if ($status == 'approve') {
                $updateDetails = [
                    'status' =>  $status,
                    'approve_at' =>  $sekarang,
                    'approve_by' =>  Auth::User()->personal_number,
                ];
                /*DB::select(" update communication_initiative set `approve_at` = '$sekarang',
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

}