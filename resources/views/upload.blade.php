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
        td, th {
            text-align: center;
        }
        tr:hover td {
            background-color: #6c757d;
        }
        th {
            min-width: 120px;
        }
        .th-click:hover {
            background-color: #6c757d;
            cursor: pointer;
        }
        th.th-ignore {
            background-color: #7e0b1b !important;
        }
        .th-ignore {
            min-width: 1px;
            max-width: 1px;
            overflow: hidden;
            font-size: 0;
        }
    </style>    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <input type="file" id="csvFile" accept=".csv" class="btn btn-sm btn-outline-dark"/>
    <button type="button" class="btn btn-sm btn-outline-danger" onclick="createTable();">Import</button>
    <button type="button" class="btn btn-sm btn-outline-warning" onclick="exportTable();">Export</button>
    <button type="button" class="btn btn-sm btn-outline-dark" onclick="turnPage(-1);">Previous</button>
    <input type="text" id="input-page" class="btn btn-sm col-1" style="color: #212529; border-color: #212529; cursor: text" onchange="specifiedPage(this.value)" placeholder="Page"/>
    <button type="button" class="btn btn-sm btn-outline-dark" onclick="turnPage(1);">Next</button>
    <table id="dataTable" class="table table table-dark table-bordered "></table>
    <script>
        var Data = {};
        var Page = 1;
        var File = '';

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
            let data = {};
            let data_group_count = -1;

            let null_row_flag = false; // 判斷每個row是否有null值
            let null_doing_flag = true; // 是否需繼續做

            let record_per_page = 25, // 每頁n筆
                now_page = 1, // 第n頁
                first_line_is_column_name = true; // 首欄為欄位名
                ignore_first_line = 0; // 忽略第前面n行
                ignore_last_line = 0; // 忽略後面n行
                row_column_type_line = 1; // 欄位型別判斷
                ignore_column = []; // 不存入的欄位

            if(typeof option !== 'undefined') {
                typeof option.record_per_page !== 'undefined' ? record_per_page : option.record_per_page;
                typeof option.now_page !== 'undefined' ? now_page : option.now_page;
                typeof option.first_line_is_column_name ? 'undefined' : option.first_line_is_column_name;
                typeof option.ignore_first_line !== 'undefined' ? ignore_first_line : option.ignore_first_line;
                typeof option.ignore_last_line !== 'undefined' ? ignore_last_line : option.ignore_last_line;
                typeof option.row_column_type_line !== 'undefined' ? row_column_type_line : option.row_column_type_line;
                typeof option.ignore_column !== 'undefined' ? ignore_column : option.ignore_column;
            }
            // 刪除忽略行數
            if(ignore_first_line > 0) array = array.splice(0, ignore_first_line);
            if(ignore_last_line > 0) array = array.splice(array.length - 1, ignore_last_line);

            // 配置html
            array.forEach(function(row, index) {
                // 首行為欄位名稱
                if( index == 0 ) {
                    data.original = {};
                    data.original.head = row;
                    data.original.body = [];
                    data.original.data = [];
                    data.export = {};
                    data.export.ignore_column = [];
                    row.forEach(function(col, i) {
                        data.export.ignore_column.push(1);
                    });
                }
                else {
                    null_row_flag = false;
                    // 分組
                    if( (index - 1) % record_per_page == 0 ) {
                        data.original.body.push([]);
                        data_group_count++;
                    }
                    data.original.body[data_group_count].push(row);
                    data.original.data.push(row);

                    // 尋找最完整的row以作為column type判斷依據
                    if( null_doing_flag === true ) {
                        row.forEach(function(cell, i) {
                            if(cell === '') null_row_flag = true;
                        });
                    }
                    if( null_row_flag === false ) null_doing_flag = false;
                    if( null_row_flag === true ) row_column_type_line++;
                }
            });
            
            data.export.example = array[row_column_type_line];
            return data;
        }
        // 渲染表格
        function renderTable(page) {
            let data = Data;
            let html = '';
            let html_head = '';
            let html_body = '';
            let head = data.original.head;
            let body = data.original.body[page - 1];

            head.forEach(function(col, i) {
                if(i == 0) html_head += `<th></th>`;
                if( data.export.ignore_column[i] == 1 ) html_head += `<th data-col="${i}" class="th-click">${col}</th>`;
                if( data.export.ignore_column[i] == 0 ) html_head += `<th data-col="${i}" class="th-click th-ignore">${col}</th>`;
            });
            html_head = `<thead><tr>${html_head}</tr></thead>`;
            body.forEach(function(row, index) {
                html_body += '<tr>';
                row.forEach(function(col, i) {
                    if(i == 0) html_body += `<td>${body.length * (page - 1) + index + 1}</td>`;
                    if( data.export.ignore_column[i] == 1 ) html_body += `<td data-col="${i}">${col}</td>`;
                    if( data.export.ignore_column[i] == 0 ) html_body += `<td data-col="${i}" class="th-ignore">${col}</td>`;
                });
                html_body += '</tr>';
            });
            html_body = `<tbody><tr>${html_body}</tr></tbody>`;

            html += html_head;
            html += html_body
            document.getElementById('dataTable').innerHTML = html;

            // 點擊切換直排顏色
            let elements = document.querySelectorAll('.th-click');
            elements.forEach(function(element) {
                element.addEventListener('click', function(e) {
                    let className = this.className;
                    let i = this.dataset.col;
                    let child = document.querySelectorAll(`[data-col='${i}']`);
                    if(className == 'th-click') {
                        child.forEach(function(c) {
                            c.classList.add("th-ignore");
                        });
                        Data.export.ignore_column[i] = 0;
                    }
                    if(className == 'th-click th-ignore') {
                        child.forEach(function(c) {
                            c.classList.remove("th-ignore");
                        });
                        Data.export.ignore_column[i] = 1;
                    }
                    console.log(Data);
                });
            });
        }
        // 建立view表格
        function createTable() {
            let csvFile = document.getElementById("csvFile");
            if( csvFile.files.length === 0 || File === csvFile.files[0].name ) return;
            else File = csvFile.files[0].name;

            let reader = new FileReader();
            let f = csvFile.files[0];
            reader.onload = function(e) {
                Data = arrayToTable(e.target.result, "data");
                console.log(Data);
                renderTable(1);
            };
            reader.readAsText(f);
        }
        
        // 輸出
        function exportTable() {
            let csvFile = document.getElementById("csvFile");
            if( Object.keys(Data).length === 0 ) return;
            let data = Data;
            let example = data.export.example;
            let ignore = data.export.ignore_column;
            let name = data.original.head;

            // total column & type
            let column_type = [];
            let column_name = [];
            example.forEach(function(row, index) {
                if(ignore[index] == 0) return;
                let type = 'varchar(max)';
                if(Number(row) != NaN) {
                    row = Number(row);
                    if( Number(row) === row && row % 1 === 0 ) type = 'int';
                    if( Number(row) === row && row % 1 !== 0 ) type = 'decimal';
                }
                column_type.push(type);
                column_name.push(name[index]);
            });

            // data
            let d = [];
            data.original.data.forEach(function(row, index) {
                if(index === 0) return;
                d.push([]);
                row.forEach(function(col, i) {
                    if(ignore[i] == 0) return;
                    d[index - 1].push(col);
                });
            });

            let headers = {
                "Content-Type": "application/json",
                "Accept": "application/json",
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
            let body = {
                'table' : document.getElementById("csvFile").files[0].name.replace('.csv', ''),
                'column' : column_name,
                'type' : column_type,
                'data' : d,
            }
            console.log(body);

            let url = `${server_url}/upload`;

            fetch(url, {
                method: 'post',
                headers: headers,
                body: JSON.stringify(body)
            })
            .then(response => response.json())
            .then((data) => {
                console.log(data.message);
            })
            .catch(error => console.error(error));

        }

        // 上下一頁
        function turnPage(page) {
            if( Object.keys(Data).length === 0 ) return;
            if( (Page + page) > 0 && (Page + page) < Data.original.body.length ) Page += page;
            pageAction(Page);
        }
        // 指定頁數
        function specifiedPage(page) {
            if( Object.keys(Data).length === 0 ) {
                document.getElementById('input-page').value = 0;
                return;
            }
            page = Number(page);
            if( page > 0 && page < Data.original.body.length ) Page = page;
            if( page < 0 ) Page = 1;
            if( page > Data.original.body.length ) Page = Data.original.body.length;
            document.getElementById('input-page').value = Page;
            pageAction(Page);
        }
        // 翻頁 action
        function pageAction(page) {
            renderTable(page);
        }
    </script>
@endsection