<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;

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
        return view('upload');
    }

    function chart() {
        return view('chart');
    }
}
