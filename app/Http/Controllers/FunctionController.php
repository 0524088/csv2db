<?php

namespace App\Http\Controllers;
use Response;
use Illuminate\Http\Request;
use Log;
use DB;

class FunctionController extends Controller
{
    function upload(Request $request) {
        DB::beginTransaction();
        try {
            // use rdbms
            $data_info = json_decode($request->input('data_info'));
            $table = $data_info->table;
            $column = $data_info->column;
            $type = $data_info->type;
            $ignore = $data_info->ignore;
            $file = $request->file('file');

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
        catch(\Exception $e) {
            DB::rollback();
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
