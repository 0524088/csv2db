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
    function createTable($table_info)
    {
        try {
            $table = $table_info['name'];
            $columns = $table_info['columns'];
            $types = $table_info['types'];
            $ignore = $table_info['ignore'];

            $sql_columns = '';
            foreach ($columns as $index => $column) {
                if ($ignore[$index] == 0)
                    continue;
                $type = $types[$index];
                $sql_columns .= "`$column` $type,";
            }
            $sql_columns = substr($sql_columns, 0, -1); // 刪去逗號

            try {
                // 處理表首
                //DB::statement("DROP TABLE IF EXISTS `$table`");
                DB::statement("CREATE TABLE `$table` ($sql_columns)");
                return ['status' => 'success', 'message' => "$table 創建成功！"];
            } catch (\Exception $e) {
                //DB::statement("DROP TABLE IF EXISTS `$table`");

                // 刪除上傳的檔案
                $file = $table_info['name'] . '.csv';
                $file_collection_name = Session::get('token');
                Storage::disk('test_file')->delete("$file_collection_name/$file");

                $error = $e->getMessage();

                if (isset($e->getPrevious()->errorInfo[0])) {
                    $error_sql_error_code = '42S01';
                    $error = "$table 已存在！";
                }

                return ['status' => 'error', 'message' => $error];
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            return ['status' => 'error', 'message' => $error];
        }
    }

    // 插入資料
    function insertData($table_info)
    {
        try {
            $ignore = $table_info['ignore']; // 忽略欄
            $ignore_start_lines = $table_info['ignore_start_lines'] + 1; // 首行為欄位名，所以要 +1
            $sql['columns'] = $table_info['columns']; // 欄位名稱
            $sql['set_value'] = $table_info['set_column_value']; // 客製化值
            $file_collection_name = Session::get('token');
            $file = $table_info['name'] . '.csv';

            foreach($sql['set_value'] as $index => $value) {
                $res = $this->checkTemplateStringFormat($value); // 檢查模板字串

                if ($res['status'] === false) {
                    Storage::disk('test_file')->delete("$file_collection_name/$file_name");
                    return ['status' => 'error', 'message' => "無效參數：{$res['matches']}"];
                }

                $matches = $res['matches'][0];
                if(count($matches) !== 0) {
                    foreach ($matches as $match)
                    {
                        $parameters_index = str_replace('@col', '', $match); // 模板字串 index

                        // 檢查是否超過欄位數
                        if($parameters_index > count($sql['set_value'])) {
                            Storage::disk('test_file')->delete("$file_collection_name/$file_name");
                            return ['status' => 'error', 'message' => "查無參數對應欄位：$match"];
                        }
                    }
                }
            }


            // 插入現有 table
            $insert_to_exist_table = $table_info['insert_to_exist_table'];
            if ($insert_to_exist_table == true) {
                $table = $table_info['insert_to_exist_table_name'];
            } else {
                $table = $table_info['name'];
            }

            $path = Storage::disk('test_file')->path("$file_collection_name/$file");
            $path = str_replace('\\', '\\\\', $path); // php反斜線為保留字，需用\\代替；MySQL\亦為保留字

            // 拚接代入
            foreach($sql['set_value'] as $index => $value) {
                if($index === 0)
                {
                    $sql_parameters = "";
                    $sql_set_parameters = "SET ";
                }
                $i = $index + 1; // 前端 index 對應
                
                // 有無忽略欄
                if($ignore[$index] == 0) {
                    $sql_parameters .= "@col{$i},";
                    continue;
                }
                $sql_parameters .= "@col{$i},";

                // 客製化參數(set value) 有: 代入, 無: 代入原始參數;
                if($value === null) {
                    $sql_set_parameters .= "`{$sql['columns'][$index]}` = @col{$i},";
                }
                else {
                    $sql_set_parameters .= "`{$sql['columns'][$index]}` = {$value},";
                }
            }

            $sql_parameters = substr($sql_parameters, 0, -1); // 刪去逗號
            $sql_set_parameters = substr($sql_set_parameters, 0, -1);

            // 匯入csv
            DB::statement("LOAD DATA INFILE '$path' INTO TABLE `$table`
            CHARACTER SET utf8mb4
            fields terminated BY ','
            lines terminated by '\\r\\n'
            ignore $ignore_start_lines lines
            ($sql_parameters)
            $sql_set_parameters");

            Storage::disk('test_file')->delete("$file_collection_name/$file"); // 匯入成功刪除檔案
            return ['status' => 'success', 'message' => "匯入資料至 $table 成功！"];
        } catch (Exception $e) {
            // DB::statement("DROP TABLE IF EXISTS `$table`");
            Storage::disk('test_file')->delete("$file_collection_name/$file");
            $error = $e->getMessage();
            return ['status' => 'error', 'message' => "insertData > $error"];
        }
    }

    // 檢查模板字串格式
    function checkTemplateStringFormat($str)
    {
        $pattern = "/@col\d+/";
        preg_match_all($pattern, $str, $matches); // 取得替代掉的字串

        if(count($matches) > 0) {
            return ['status' => true, 'matches' => $matches];
        } else {
            return ['status' => false, 'matches' => $matches[0]];
        }
    }


    // 取得全部 table 名
    function getTablesName()
    {
        try {
            $db_name = env('DB_DATABASE');
            $sql = "SELECT table_name 
                    FROM (SELECT table_name FROM information_schema.tables WHERE table_schema = ?) AS t 
                    WHERE table_name <> 'users'";
            $tables = DB::select($sql, [$db_name]);
            return ['status' => 'success', 'data' => $tables];
        } catch (Exception $e) {
            $error = $e->getMessage();
            return ['status' => 'error', 'message' => $error];
        }
    }

    // 取得 table 下的 column 資訊
    function getTableColumnsInfo($table_name)
    {
        try {
            $db_name = env('DB_DATABASE');
            $sql = "SELECT COLUMN_NAME, DATA_TYPE
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_NAME = ?";
            $columns_info = DB::select($sql, [$table_name]);
            return ['status' => 'success', 'data' => $columns_info];
        } catch (Exception $e) {
            $error = $e->getMessage();
            return ['status' => 'error', 'message' => $error];
        }
    }
}