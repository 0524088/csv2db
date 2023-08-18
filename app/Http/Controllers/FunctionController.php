<?php

namespace App\Http\Controllers;
use Response;
use Illuminate\Http\Request;

class FunctionController extends Controller
{
    function upload(Request $request) {
        try {
            $table = $request->input('table');
            $column = $request->input('column');
            $type = $request->input('type');
            $data = $request->input('data');
            return response(['status' => 'success', 'message' => "$table is created success!"], 200);
        }
        catch(\Exception $e) {
            $error = $e->getMessage();
            $error = $e->getResponse()->getBody()->getContents();
            return dd($error);
            return response(['status' => 'error', 'message' => $error], 200);
        }
    }
}
