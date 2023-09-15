<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use DB;

class ViewController extends Controller
{
    function index() {
        return view('index');
    }

    function login() {
        if(Session::has('token')) return redirect('index');
        else return view('login');
    }

    function logout() {
        Session::forget('token');
        return redirect('index');
    }

    function upload() {
        $db_name = env('DB_DATABASE');
        $tables = app('App\Http\Functions\DatabaseManipulate')->getTablesName();
        if($tables['status'] === 'success') {
            $tables = $tables['data'];
            return view('upload', compact('tables'));
        } else {
            $data = [
                'type' => 'danger',
                'message' => $tables['message']
            ];
            return view('error')->with($data);
        }
    }

    function chart() {
        return view('chart');
    }
}
