@extends('layouts.base')
@section('title', '上傳')
@section('content')
    <style>
        #csvFile {
            margin-top: 1vh;
            margin-bottom: 1vh;
        }
        table {
            max-height: 83vh;
            overflow: auto;
            display:inline-block;
        }
        table td th{
            text-align: center;
        }
        table tbody tr:hover td {
            background-color: #6c757d;
        }
        table th {
            min-width: 120px;
        }
    </style>
    <input type="file" id="csvFile" accept=".csv" class="btn btn-sm btn-outline-secondary"/>
    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="createTable()">Import</button>
    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="turnPage(-1);">previous</button>
    <input type="text" id="input-pages" class="btn btn-sm btn-outline-secondary" onchange="specifiedPage(this.value)" placeholder="Pages"/>
    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="turnPage(1);">next</button>
    <table id="dataTable" class="table table table-dark table-bordered "></table>
    <script>
        let Data;
        let Pages = 1;

        // csv轉陣列
        function csvToArray(result) {
            let resultArray = [];
            result.split("\n").forEach(function(row) {
                if (row) {
                    row = row.trimEnd();
                    let rowArray = [];
                    row.split(",").forEach(function(cell) {
                        rowArray.push(cell);
                    });
                    resultArray.push(rowArray);
                }
            });
            return resultArray;
        }

        // 轉換html
        function arrayToTable(result, keyName, option) {
            var array = csvToArray(result); //this is where the csv array will be
            let html = {};
            let html_body_count = -1;

            let record_per_pages = 25, // 每頁n筆
                now_page = 1, // 第n頁
                first_line_is_column_name = true; // 首欄為欄位名
                ignore_first_line = 0; // 忽略第前面n行
                ignore_last_line = 0; // 忽略後面n行

            if(typeof option !== 'undefined') {
                typeof option.record_per_pages !== 'undefined' ? record_per_pages : option.record_per_pages;
                typeof option.now_page !== 'undefined' ? now_page : option.now_page;
                typeof option.first_line_is_column_name ? 'undefined' : option.first_line_is_column_name;
                typeof option.ignore_first_line !== 'undefined' ? ignore_first_line : option.ignore_first_line;
                typeof option.ignore_last_line !== 'undefined' ? ignore_last_line : option.ignore_last_line;
            }
            // 刪除忽略行數
            if(ignore_first_line > 0) array = array.splice(0, ignore_first_line);
            if(ignore_last_line > 0) array = array.splice(array.length - 1, ignore_last_line);

            // 配置html
            array.forEach(function(row, index) {
                // 首行為欄位名稱
                if( index == 0 ) {
                    html.head = '';
                    html.body = [];
                    row.forEach(function(cell, i) {
                        if(i == 0) html.head += `<th></th>`;
                        html.head += `<th>${cell}</th>`;
                    });
                    html.head = `<thead><tr>${html.head}</tr></thead>`;
                }
                else {
                    // 分組
                    if( (index - 1) % record_per_pages == 0 ) {
                        html_body_count++;
                        html.body[html_body_count] = '';
                    }
                    html.body[html_body_count] += '<tr>';
                    row.forEach(function(cell, i) {
                        if(i == 0) html.body[html_body_count] += `<td>${index}</td>`;
                        html.body[html_body_count] += `<td>${cell}</td>`;
                    });
                    html.body[html_body_count] += '</tr>';
                }

            });

            let h = html.head;
            h += `<tbody>${html.body[now_page - 1]}</tbody>`;
            document.getElementById("dataTable").innerHTML = h;
            return html;
        }

        function createTable() {
            let csvFile = document.getElementById("csvFile");
            let reader = new FileReader();
            let f = csvFile.files[0];

            reader.onload = function(e) {
                Data = arrayToTable(e.target.result, "data");
                console.log(Data);

            };
            reader.readAsText(f);
        }
        
        // 上下一頁
        function turnPage(pages) {
            if( typeof Data === 'undefined' ) return;
            if( (Pages + pages) > 0 && (Pages + pages) < Data.body.length ) Pages += pages;
            page(pages)
        }
        // 指定頁數
        function specifiedPage(pages) {
            if( typeof Data === 'undefined' ) return;
            if( pages > 0 && pages < Data.body.length ) Pages = pages;
            if( pages < 0 ) Pages = 1;
            if( pages > Data.body.length ) Pages = Data.body.length;
            document.getElementById('input-pages').value = Pages;
            page(Pages)
        }
        // 翻頁 action
        function page(pages) {
            console.log(Data);
            if( typeof Data === 'undefined' ) return;
            let h = Data.head;
            h += `<tbody>${Data.body[Pages - 1]}</tbody>`;
            document.getElementById("dataTable").innerHTML = h;
        }
    </script>
@endsection