<?php

namespace App\Http\Controllers;

use Response;
use Illuminate\Http\Request;
use Log;
use DB;
use Storage;
use Illuminate\Support\Str;
use Session;
use Exception;

class AjaxController extends Controller
{
    // 上傳檔案 (分割檔)
    function upload(Request $request) {
        try {
            $base64data = $request->input('base64data');
            $file_name = $request->input('name');
            $num = $request->input('num');
            $file_save_name = $file_name.'_'.$num;
            $file_collection_name = Session::get('token');

            Storage::disk('test_file')->put("$file_collection_name/$file_save_name", $base64data); // 存放檔案

            return response([
                'status' => 'success',
                'message' => $file_save_name." uploaded success",
                'original_file_name' => $file_name,
                'chunk_file_name' => $file_save_name,
            ], 200);
        } catch(\Exception $e) {
            $error = $e->getMessage();
            return response(['status' => 'error', 'message' => $error], 200);
        }
    }

    // 合併分割檔
    function upload_finished(Request $request) {
        try {
            $count = $request->input('count');
            $file_collection_name = Session::get('token');
            $total = count(Storage::disk('test_file')->files("$file_collection_name")); // 總檔案數
            $file_name = $request->input('name');
            $insert_to_exist_table =  $request->input('insert_to_exist_table');

            if( $count == $total ) {
                $write_flag = false;
                // 依序讀入分割檔
                for( $i = 0; $i < $total; $i++ ) {
                    $write_flag = true;
                    $file_save_name = $file_name.'_'.$i;
                    // 讀檔
                    $file_r = Storage::disk('test_file')->path("$file_collection_name/$file_save_name");
                    $buff = file_get_contents($file_r);

                    // 刪除
                    Storage::disk('test_file')->delete("$file_collection_name/$file_save_name");

                    // 寫進檔案 (拼接)
                    $file_w = Storage::disk('test_file')->path("$file_collection_name/$file_name.csv");
                    file_put_contents($file_w, $buff, FILE_APPEND);
                }
                // 無分割檔
                if(!$write_flag) {
                    // 改檔名
                    $file_w = Storage::disk('test_file')->path("$file_collection_name/$file_name.csv");
                    file_put_contents($file_w);

                }

                // base64轉回
                $file_r = Storage::disk('test_file')->path("$file_collection_name/$file_name.csv");
                $buff = base64_decode(str_replace('data:text/csv;base64,', '', file_get_contents($file_r)));
                $file_w = Storage::disk('test_file')->path("$file_collection_name/$file_name.csv");
                file_put_contents($file_w, $buff);


                $ignore_start_lines =  $request->input('ignore_start_lines');
                $ignore_end_lines =  $request->input('ignore_end_lines');
                // 刪去後 n 行
                $lines = file($file_w);
                for($i = 0; $i < $ignore_end_lines; $i++) {
                    $last = sizeof($lines) - 1;
                    unset($lines[$last]);
                }
                file_put_contents($file_w, $lines);

                /* 列出最後一行的文字
                $fp = fopen($file_w, 'r+');
                $cursor = -1;
                $line = '';
                $char = fgets($fp);
                while ($char === "\n" || $char === "\r") {
                    fseek($fp, $cursor--, SEEK_END);
                    $char = fgets($fp);
                }
                while ($char !== false && $char !== "\n" && $char !== "\r") {
                    $line = $char.$line;
                    fseek($fp, $cursor--, SEEK_END);
                    $char = fgets($fp);
                }
                */
 
                $table_info = [
                    'name' => $request->input('name'),
                    'columns' => $request->input('column'),
                    'types' => $request->input('type'),
                    'ignore' => $request->input('ignore'),
                    'ignore_start_lines' => $ignore_start_lines,
                    'ignore_end_lines' => $ignore_end_lines,
                    'insert_to_exist_table' => $insert_to_exist_table,
                    'insert_to_exist_table_name' => $request->input('insert_to_exist_table_name'),
                ];

                // 創建table
                if($insert_to_exist_table == false) {
                    $create_table_result = app('App\Http\Functions\DatabaseManipulate')->createTable($table_info);
                    if($create_table_result['status'] === 'success') {
                        // 讀取csv並存入table
                        $insert_table_result = app('App\Http\Functions\DatabaseManipulate')->insertData($table_info);
                        if($insert_table_result['status'] === 'success') {
                            return response(['status' => 'success', 'message' => '上傳成功！'], 200);
                        }
                        return response($insert_table_result);
                    }
                    return response($create_table_result);
                } else {
                    // 讀取csv並存入table
                    $insert_table_result = app('App\Http\Functions\DatabaseManipulate')->insertData($table_info);
                    if($insert_table_result['status'] === 'success') {
                        return response(['status' => 'success', 'message' => '上傳成功！'], 200);
                    }
                    return response($insert_table_result);
                }

            }
            if( $count < $total ) return response(['status' => 'error', 'message' => '遺失檔案！', 'quantity' => ($count - $total)], 400); // 少分割數量 (有上傳失敗)
            if( $count > $total ) return response(['status' => 'error', 'message' => '多餘檔案！', 'quantity' => ($count - $total)], 400); // 多分割數量 (其他錯誤，多出檔案)
        } catch(Exception $e) {
            $error = $e->getMessage();
            return response(['status' => 'error', 'message' => $error], 400);
        }
    }

    // 取得全部 table 名
    function getTablesName() {
        try {
            $tables = app('App\Http\Functions\DatabaseManipulate')->getTablesName();
            if($tables['status'] === 'success') {
                return response(['status' => 'success' , 'data' => $tables['data']], 200);
            } else {
                return response(['status' => 'error', 'data' => $tables['message']], 400);
            }
        } catch(Exception $e) {
            $error = $e->getMessage();
            return response(['status' => 'error', 'message' => $error], 400);
        }
    }

    // 取得 table 下的 column 資訊
    function getTableColumnsInfo(Request $request) {
        try {
            $table_name = $request->input('table_name');
            $columns_info = app('App\Http\Functions\DatabaseManipulate')->getTableColumnsInfo($table_name);
            if($columns_info['status'] === 'success') {
                return response(['status' => 'success', 'data' => $columns_info['data']], 200);
            } else {
                return response(['status' => 'error', 'data' => $columns_info['message']], 400);
            }
        } catch(Exception $e) {
            $error = $e->getMessage();
            return response(['status' => 'error', 'message' => $error], 400);
        }
    }
}
