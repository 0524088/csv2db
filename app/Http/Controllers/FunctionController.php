<?php

namespace App\Http\Controllers;
use Response;
use Illuminate\Http\Request;
use Log;
use DB;
use Storage;
use Illuminate\Support\Str;
use Session;

class FunctionController extends Controller
{
    // 上傳檔案(分割檔)
    function upload(Request $request) {
        try {
            $file = $request->file('file');
            $file_name = $request->input('name');
            $num = $request->input('num');
            $file_collection_name = Session::get('token');

            Storage::disk('test_file')->putFileAs('', $file, "$file_collection_name/$file_name".'_'.$num); // 存放檔案

            return response([
                'status' => 'success',
                'message' => $file_name.'_'.$num."uploaded success",
                'original_file_name' => $file_name,
                'chunk_file_name' => $file_name.'_'.$num,
            ], 200);
        }
        catch(\Exception $e) {
            $error = $e->getMessage();
            return response(['status' => 'error', 'message' => $error], 200);
        }
    }

    // 合併分割檔
    function upload_finished(Request $request) {
        try {
            $count = $request->input('count');
            $file_collection_name = Session::get('token');
            $total = count(Storage::disk('test_file')->files($file_collection_name)); // 總檔案數

            $file_name = $request->input('name');
            $column = $request->input('column');
            $type = $request->input('type');
            $ignore = $request->input('ignore');

            if( $count == $total ) {
                $write_flag = false;
                // 依序讀入分割檔
                for( $i = 0; $i < $total; $i++ ) {
                    $write_flag = true;
                    // 讀檔
                    $file_r = Storage::disk('test_file')->path("$file_collection_name/$file_name".'_'.$i);
                    $buff = file_get_contents($file_r);

                    // 刪除
                    Storage::disk('test_file')->delete("$file_collection_name/$file_name".'_'.$i);

                    // 寫進檔案
                    $file_w = Storage::disk('test_file')->path("$file_collection_name").'/'.$file_name.'.csv';
                    file_put_contents($file_w, $buff, FILE_APPEND);
                }
                // 無分割檔
                if(!$write_flag) {
                    // 改檔名
                    $file_w = Storage::disk('test_file')->path("$file_collection_name").'/'.$file_name.'.csv';
                    file_put_contents($file_w);
                }

            }

            if( $count == $total ) return response(['status' => 'success', 'message' => 'uploaded success'], 200); // 分割數量相同
            if( $count < $total ) return response(['status' => 'error', 'message' => 'lose files!', 'quantity' => ($count - $total)], 400); // 少分割數量(有上傳失敗)
            if( $count > $total ) return response(['status' => 'error', 'message' => 'extra files!', 'quantity' => ($count - $total)], 400); // 多分割數量(其他錯誤，多出檔案)
        }
        catch(\Exceoption $e) {
            $error = $e->getMessage();
            return response(['status' => 'error', 'message' => $error], 400);
        }
    }

    function test() {
        DB::beginTransaction();
        try {
            $file_handle = fopen($file, 'r');
            $columns = '';
            $row_count = 0;
            $ps_col = ''; // 參數式 - 欄
            $ps_val = []; // 參數式 - 值
            $ps_total = 0; // 參數式 - 單筆資料長度

            while (!feof($file_handle)) {
                $col_count = -1; // 判斷是否為首行
                $ps_count = 0;
                $data = fgetcsv($file_handle); // 讀取新一行
                if(!$data) continue; // 錯誤不做
                foreach($data as $index => $value) {
                    $col_count++;
                    // 處理表首
                    if( $row_count === 0 ) {
                        if( $ignore[$col_count] == 0 ) continue; // 略過此欄
                        $t = $type[$col_count];
                        $columns .= "`$value` $t,"; // column_name + datatype
                        $ps_total++; // total column
                    }
                    // 處理資料
                    else {
                        if( $ignore[$col_count] == 0 ) continue; // 略過此欄
                        array_push($ps_val, $value);
                        $ps_count++;
                        if( $ps_count === 1 ) $ps_col .= '(';
                        $ps_col .= '?,';
                        if( $ps_count % $ps_total === 0 ) {
                            $ps_col = substr($ps_col, 0, -1); // 刪去逗號
                            $ps_col .= '),';
                        }
                        
                    }
                    $col_count++;
                }
                try {
                    // 處理表首
                    if($row_count === 0) {
                        $columns = substr($columns, 0, -1); // 刪去逗號
                        DB::select("CREATE TABLE `$table` ($columns)");
                    }
                    $row_count++;
                }
                catch(\Exception $e) {
                    DB::delete("DROP TABLE IF EXISTS `$table`");
                    $error = $e->getMessage();
                    return response(['status' => 'error', 'message' => $error], 200);
                }
            }
            fclose($file_handle);
            $ps_col = substr($ps_col, 0, -1); // 刪去逗號

            DB::insert("INSERT INTO `$table` VALUES $ps_col", $ps_val);
            DB::commit();

            return response(['status' => 'success', 'message' => "$table is created success!"], 200);
        }
        catch(\Exceoption $e) {
            DB::rollback();
            $error = $e->getMessage();
            return response(['status' => 'error', 'message' => $error], 400);
        }
    }
}
