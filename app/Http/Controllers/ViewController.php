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
        $tables = DB::select("SELECT table_name 
                                FROM (SELECT table_name FROM information_schema.tables WHERE table_schema = 'csv2db') as t WHERE table_name <> 'users'");
        return view('upload', compact('tables'));
    }

    function chart() {
        return view('chart');
    }
}
