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
    // 上傳檔案 (分割檔)
    function upload(Request $request) {
        try {
            $base64data = $request->input('base64data');
            $file_name = $request->input('name');
            $num = $request->input('num');
            $file_collection_name = Session::get('token');

            $file_w = Storage::disk('test_file')->path("$file_collection_name/$file_name".'_'.$num);
            file_put_contents($file_w, $base64data);

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

                $table_info = [
                    'name' => $request->input('name'),
                    'columns' => $request->input('column'),
                    'types' => $request->input('type'),
                    'ignore' => $request->input('ignore')
                ];
                // 創建table
                $create_table_result = $this->createTable($table_info);
                if($create_table_result['status'] === 'success') {
                    // 讀取csv並存入table
                    $insert_table_result = $this->insertData($table_info);
                    if($insert_table_result['status'] === 'success') {
                        return response(['status' => 'success', 'message' => 'uploaded success!'], 200);
                    }
                }
            }
            if( $count < $total ) return response(['status' => 'error', 'message' => 'lose files!', 'quantity' => ($count - $total)], 400); // 少分割數量 (有上傳失敗)
            if( $count > $total ) return response(['status' => 'error', 'message' => 'extra files!', 'quantity' => ($count - $total)], 400); // 多分割數量 (其他錯誤，多出檔案)
        }
        catch(\Exceoption $e) {
            $error = $e->getMessage();
            return response(['status' => 'error', 'message' => $error], 400);
        }
    }

    function createTable($table_info) {
        try {
            $table = $table_info['name'];
            $columns = $table_info['columns'];
            $types = $table_info['types'];
            $ignore = $table_info['ignore'];

            $sql_columns = '';
            foreach($columns as $index => $column) {
                if($ignore[$index] == 0) continue;
                $type = $types[$index];
                $sql_columns .= "`$column` $type,";
            }
            $sql_columns = substr($sql_columns, 0, -1); // 刪去逗號

            try {
                // 處理表首
                DB::statement("CREATE TABLE `$table` ($sql_columns)");
                return ['status' => 'success', 'message' => "$table is created success!"];
            }
            catch(\Exception $e) {
                DB::statement("DROP TABLE IF EXISTS `$table`");
                $error = $e->getMessage();
                return ['status' => 'error', 'message' => $error];
            }
        }
        catch(\Exceoption $e) {
            $error = $e->getMessage();
            return ['status' => 'error', 'message' => $error];
        }
    }


    function insertData($table_info) {
        try {
            $table = $table_info['name'];
            $columns = $table_info['columns'];
            $ignore = $table_info['ignore'];

            $file_collection_name = Session::get('token');
            $file = $table.'.csv';
            $path = Storage::disk('test_file')->path("$file_collection_name/$file");
            $path = str_replace('\\', '\\\\', $path); // php反斜線為保留字，需用\\代替；MySQL\亦為保留字
            
            // 拼接代入column
            $sql_parameters = '';
            foreach($columns as $index => $column) {
                if($ignore[$index] == 0) {
                    $sql_parameters .= "@temp,";
                } else {
                    $sql_parameters .= "`$column`,";
                }
            }
            $sql_parameters = substr($sql_parameters, 0, -1); // 刪去逗號

            // 匯入csv
            DB::statement("LOAD DATA INFILE '$path' INTO TABLE `$table`
            CHARACTER SET utf8mb4
            fields terminated BY ','
            lines terminated by '\\r\\n'
            ignore 1 lines
            ($sql_parameters)");

            Storage::disk('test_file')->delete("$file_collection_name/$file"); // 匯入成功刪除檔案
            return ['status' => 'success', 'message' => "inserted data into $table success!"];
        }
        catch(\Exceoption $e) {
            DB::statement("DROP TABLE IF EXISTS `$table`");
            Storage::disk('test_file')->delete("$file_collection_name/$file");
            $error = $e->getMessage();
            return ['status' => 'error', 'message' => $error];
        }
    }
}
