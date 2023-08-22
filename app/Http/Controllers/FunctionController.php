<?php

namespace App\Http\Controllers;
use Response;
use Illuminate\Http\Request;
use Log;
use DB;

class FunctionController extends Controller
{
    function upload(Request $request) {
        try {
            $data_info = json_decode($request->input('data_info'));
            $table = $data_info->table;
            $column = $data_info->column;
            $type = $data_info->type;
            $file = $request->file('file');
                     
            // use sql sp
                DB::connection('mongodb')       //选择使用mongodb
                      ->collection('test')           //选择使用users集合
                      ->insert([                          //插入数据
                              'name'  =>  'tom', 
                              'age'     =>   18
                          ]);
        
            $res = DB::connection('mongodb')->collection('test')->all();   //查询所有数据
            return dd($res);
        


            // 2 use php
            $file_handle = fopen($file, 'r');
            $count = 0;
            while (!feof($file_handle)) {
                $line_of_text[] = fgetcsv($file_handle, 0, ',');
                Log::info($count);
                $count++;
            }
            fclose($file_handle);
            return response(['status' => 'success', 'message' => '檔案處理中'], 200);








            return response(['status' => 'success', 'message' => "$table is created success!"], 200);
        }
        catch(\Exception $e) {
            $error = $e->getMessage();
            return response(['status' => 'error', 'message' => $error], 200);
        }
    }

    function upload_process_bar() {
        try {
            return response(['status' => 'success', 'message' => "success"], 200);
        }
        catch(\Exception $e) {
            return response(['status' => 'error', 'message' => $error], 200);

        }
    }

}
