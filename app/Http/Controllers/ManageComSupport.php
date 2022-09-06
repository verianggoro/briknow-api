<?php

namespace App\Http\Controllers;

use App\CommunicationInitiative;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

            $search = "";
            if($request->get('search')) {
                $search = $request->get('search');
                $model->where('title', 'like','%'.$request->get('search').'%');
            }

            $data = $model->where('type_file', $type)->where('status', '!=', 'deleted')->skip($offset)->take($limit)->get();
            /*if($request->get('sort')) {
                $data = CommunicationInitiative::where('type_file', $type)
                    ->where('title', 'like', "%$search%")
                    ->orderBy('created_at', $order)
                    ->skip($offset)->take($limit)->get();
            } else {
                $data = CommunicationInitiative::where('type_file', $type)
                    ->where('title', 'like', "%$search%")
                    ->skip($offset)->take($limit)->get();
            }*/

            $count = count($data);
            $countTotal = CommunicationInitiative::where('type_file', $type)->count();

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

    public function setStatus($status, $id) {
        try {
            $sekarang = Carbon::now();

            if ($status == 'Approve') {
                $updateDetails = [
                    'status' =>  $status,
                    'approve_at' =>  $sekarang
                ];
                /*DB::select(" update communication_initiative set `approve_at` = '$sekarang',
                                     `approve_by` = '$user', `status` = '$status' where id = '$id' ");*/
            } else if ($status == 'Publish') {
                $updateDetails = [
                    'status' =>  $status,
                    'publish_at' =>  $sekarang
                ];
            } else if ($status == 'Reject') {
                $updateDetails = [
                    'status' =>  $status,
                    'reject_at' =>  $sekarang
                ];
            } else {
                $updateDetails = [
                    'status' =>  $status
                ];
            }
            CommunicationInitiative::where('id', $id)
                ->update($updateDetails);

            $data['message']    =   $status.' Proyek Berhasil.';
            $data['toast']    =   $status == 'Publish' ? 'Proyek berhasil diterbitkan' : $status.' Berhasil.';
            return response()->json([
                "status"    => 1,
                "data"      => $data,
            ],200);
        } catch (\Throwable $th) {
            $data['message']    =   $status.' Proyek Gagal';
            $data['toast']    =   $status == 'Publish' ? 'Project gagal diterbitkan!' : $status.' Proyek Gagal.';
            return response()->json([
                'status'    =>  0,
                'data'      =>  $data
            ],200);
        }
    }

    public function delete($id) {
        try {
            $sekarang = Carbon::now();

            CommunicationInitiative::where('id', $id)
                ->update(['status' => 'deleted', 'deleted_at' => $sekarang]);

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