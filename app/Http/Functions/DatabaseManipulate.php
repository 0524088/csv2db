<?php

namespace App\Http\Functions;
use Response;
use Log;
use DB;
use Storage;
use Illuminate\Support\Str;
use Session;
use Exception;

class DatabaseManipulate
{


    // 建立表首
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
            } catch(\Exception $e) {
                //DB::statement("DROP TABLE IF EXISTS `$table`");
                $file = $table_info['name'].'.csv';
                $file_collection_name = Session::get('token');
                Storage::disk('test_file')->delete("$file_collection_name/$file");
                $error = $e->getMessage();
                return ['status' => 'error', 'message' => $error];
            }
        } catch(Exception $e) {
            $error = $e->getMessage();
            return ['status' => 'error', 'message' => $error];
        }
    }

    // 插入資料
    function insertData($table_info) {
        try {
            
            $columns = $table_info['columns'];
            $ignore = $table_info['ignore'];
            $ignore_start_lines = $table_info['ignore_start_lines'] + 1; // 首行為欄位名，所以要 +1

            // 插入現有 table
            $insert_to_exist_table = $table_info['insert_to_exist_table'];
            if($insert_to_exist_table == true) {
                $table = $table_info['insert_to_exist_table_name'];
            } else {
                $table = $table_info['name'];
            }
            
            $file_collection_name = Session::get('token');
            $file = $table_info['name'].'.csv';
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
            ignore $ignore_start_lines lines
            ($sql_parameters)");

            Storage::disk('test_file')->delete("$file_collection_name/$file"); // 匯入成功刪除檔案
            return ['status' => 'success', 'message' => "inserted data into $table success!"];
        } catch(Exception $e) {
            //DB::statement("DROP TABLE IF EXISTS `$table`");
            Storage::disk('test_file')->delete("$file_collection_name/$file");
            $error = $e->getMessage();
            return ['status' => 'error', 'message' => $error];
        }
    }
    
    // 取得全部 table 名
    function getTablesName() {
        try {
            $db_name = env('DB_DATABASE');
            $tables = DB::select("SELECT table_name 
                                    FROM (SELECT table_name FROM information_schema.tables WHERE table_schema = '$db_name') as t WHERE table_name <> 'users'");
            return ['status' => 'success', 'data' => $tables];
        } catch(Exception $e) {
            $error = $e->getMessage();
            return ['status' => 'error', 'message' => $error];
        }
    }

    // 取得 table 下的 column 資訊
    function getTableColumnsInfo($table_name) {
        try {
            $db_name = env('DB_DATABASE');
            $columns_info = DB::select("SELECT COLUMN_NAME, DATA_TYPE
                                            FROM INFORMATION_SCHEMA.COLUMNS
                                            WHERE TABLE_NAME = '$table_name';");
            return ['status' => 'success', 'data' => $columns_info];
        } catch(Exception $e) {
            $error = $e->getMessage();
            return ['status' => 'error', 'message' => $error];
        }
    }
}
