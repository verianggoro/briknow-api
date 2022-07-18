<?php

namespace App\Http\Controllers;

use App\Favorite_consultant;
use Auth;
use Illuminate\Http\Request;

class FavoriteConsultantController extends Controller
{
    public function index()
    {
        //
    }

    public function save($id)
    {
        $query = Favorite_consultant::where('consultant_id', $id)->where('user_id', Auth::user()->id)->first();
        // return $query;
        if ($query) { //hapus favorit jika ada
            try {
                $query->delete();
                $data['kondisi']    =   0;
                $data['message']    =   "Data berhasil dihapus.";
                return response()->json([
                    "status"    =>  1,
                    "data"      => $data
                ],200);
            } catch (\Throwable $th) {
                return response()->json([
                    "status"    =>  0,
                    "message"   =>  "Data gagal dihapus.",
                ],500);
            }
        } else { //add favorit jika belum
            try {
                $favorit = new Favorite_consultant;
                $favorit->user_id = Auth::user()->id;
                $favorit->consultant_id = $id;
                $favorit->save();

                $data['kondisi']    =   1;
                $data['message']    =   "Data Berhasil Ditambahkan.";
                return response()->json([
                    "status"    =>  1,
                    "data"      => $data
                ],200);
            } catch (\Throwable $th) {
                return response()->json([
                    "status"    =>  0,
                    "message"   =>  "Data gagal ditambahkan.",
                ],500);
            }
        }
    }
}
