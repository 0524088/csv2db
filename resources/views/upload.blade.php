@extends('layouts.base')
@section('title', '上傳')
@section('content')
    <style>
        #csvFile {
            margin-top: 1vh;
            margin-bottom: 1vh;
        }
        #dataTable {
            max-height: 75vh;
            overflow: auto;
            display:inline-block;
        }
        #dataTable td, th {
            text-align: center;
        }
        #dataTable tr:hover td {
            background-color: #6c757d;
        }
        th {
            min-width: 120px;
        }
        #dataTable .th-parent:hover {
            background-color: #6c757d;
            cursor: pointer;
        }
        #dataTable th.th-ignore {
            background-color: #7e0b1b ;
        }
        #dataTable .th-ignore {
            min-width: 1px;
            max-width: 1px;
            overflow: hidden;
            font-size: 0;
        }

        .question-icon {
            cursor: pointer;
        }

    </style>

    <input type="file" id="csvFile" accept=".csv" onchange="fileChange();" class="btn btn-sm btn-outline-dark"/>
    <button id="btn-import" type="button" class="btn btn-sm btn-outline-danger" onclick="createTable();" disabled>Import</button>
    <button id="btn-export" type="button" class="btn btn-sm btn-outline-warning btn-lg" onclick="exportTable();" disabled>Export</button>
    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#tableSetting">Table Setting</button>
    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#columnSetting">Column Setting</button>

    <div class="progress" style="width: 50%">
        <div id="progress_bar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 0%; color: black">0 %</div>
    </div>
    
    <div class="" style="margin-top: 10px; margin-bottom: 5px;">
        <button type="button" class="btn btn-sm btn-outline-dark" onclick="turnPage(-1);">Previous</button>
        <input type="text" id="input-page" class="btn btn-sm col-1" style="color: #212529; border-color: #212529; cursor: text" onchange="specifiedPage(this.value)" placeholder="Page"/>
        <button type="button" class="btn btn-sm btn-outline-dark" onclick="turnPage(1);">Next</button>
    </div>
    <table id="dataTable" class="table table table-dark table-bordered "></table>


<div class="modal fade" id="tableSetting" data-bs-keyboard="true" tabindex="-1" aria-labelledby="tableSetting" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tableSetting">Table Setting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tab-content">
                <div class="row">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="recordPerPage" checked>
                            <label class="form-check-label" for="recordPerPage">
                                每頁
                                <select class="" id="setting_table_record_per_page">
                                    <option value="25" checked>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                筆資料
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="addToExistTable">
                            <label class="form-check-label" for="addToExistTable">
                                匯入到 Table&nbsp;
                                <select class="" id="">
                                    @foreach ( $tables as $table )
                                    <option value="{{ $table->TABLE_NAME }}">{{ $table->TABLE_NAME }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="ignoreFirstLine">
                            <label class="form-check-label" for="ignoreFirstLine">
                                省略第一行
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="columnSetting" data-bs-keyboard="true" tabindex="-1" aria-labelledby="columnSetting" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="columnSetting">Column Setting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tab-content">
                <div id="setting_column_content">
                    <table class="table" style="overflow-x: auto; display:inline-block;">
                        <thead>
                            <tr>
                                <th scope="col">Using</th>
                                <th scope="col">#</th>
                                <th scope="col">Column Name</th>
                                <th scope="col">Data Type</th>
                                <th scope="col">
                                    Set Value
                                    <svg xmlns="http://www.w3.org/2000/svg" class="question-icon" data-toggle="tooltip" data-placement="top" title="Tooltip on top" width="16" height="16" fill="currentColor" class="bi bi-question-circle" viewBox="0 0 16 16">
                                        <title>將欄位值做額外計算（需視其data type而定）</title>
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"/>
                                    </svg>
                                </th>
                                <th scope="col">
                                    Regular Expression
                                    <svg xmlns="http://www.w3.org/2000/svg" class="question-icon" data-toggle="tooltip" data-placement="top" title="Tooltip on top" width="16" height="16" fill="currentColor" class="bi bi-question-circle" viewBox="0 0 16 16">
                                        <title>將欄位值套用格式清理</title>
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"/>
                                    </svg>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="setting_column_table"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
    <script>
        var Data = {};
        var Page = 1;
        var File = '';

        // ====================================================================================================================================
        // file button
        // 切換檔案
        function fileChange() {
            progree = document.getElementById('progress_bar');
            progree.innerHTML = `0 %`;
            progree.style.width = `0%`;

            let csvFile = document.getElementById("csvFile");
            if( csvFile.files.length === 0 || File === csvFile.files[0].name ) {
                document.getElementById('btn-import').disabled = false;
                document.getElementById('btn-export').disabled = true;
            }
            else {
                document.getElementById('btn-import').disabled = false;
                document.getElementById('btn-export').disabled = true;
                document.getElementById('dataTable').innerHTML = '';
                Page = 1;
                document.getElementById('input-page').value = '';
            }
        }
        

        // ====================================================================================================================================
        // Import Button
        // 建立view表格
        function createTable() {
            document.getElementById('btn-import').disabled = true;
            document.getElementById('btn-export').disabled = false;

            let csvFile = document.getElementById("csvFile");
            if( csvFile.files.length === 0 || File === csvFile.files[0].name ) return;
            else File = csvFile.files[0].name;

            let reader = new FileReader();
            let f = csvFile.files[0];
            reader.onload = function(e) {
                Data = arrayToTable(e.target.result, "data");
                console.log(Data);
                renderSetting();
                renderTable(1);
            };
            reader.readAsText(f);
        }
        
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
                    data.original.group_count = record_per_page;
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
            data.export.type = [];
            data.export.name = data.original.head;

            let example = data.export.example;
            let types = data.export.type;
            example.forEach(function(row, index) {
                let type = 'text';
                if(Number(row) != NaN) {
                    row = Number(row);
                    if( Number(row) === row && row % 1 === 0 ) type = 'int';
                    if( Number(row) === row && row % 1 !== 0 ) type = 'decimal';
                }
                types.push(type);
            });

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
            let group = data.original.group_count;

            head.forEach(function(col, i) {
                if(i == 0) html_head += `<th></th>`;
                if( data.export.ignore_column[i] == 1 ) html_head += `<th data-col="${i}" id="th-parent-${i}" class="th-parent">${col}</th>`;
                if( data.export.ignore_column[i] == 0 ) html_head += `<th data-col="${i}" id="th-parent-${i}" class="th-parent th-ignore">${col}</th>`;
            });
            html_head = `<thead><tr>${html_head}</tr></thead>`;
            body.forEach(function(row, index) {
                html_body += '<tr>';
                row.forEach(function(col, i) {
                    if(i == 0) html_body += `<td>${group * (page - 1) + index + 1}</td>`;
                    if( data.export.ignore_column[i] == 1 ) html_body += `<td data-col="${i}" class="th-child-${i}">${col}</td>`;
                    if( data.export.ignore_column[i] == 0 ) html_body += `<td data-col="${i}" class="th-child-${i} th-ignore">${col}</td>`;
                });
                html_body += '</tr>';
            });
            html_body = `<tbody><tr>${html_body}</tr></tbody>`;

            html += html_head;
            html += html_body
            document.getElementById('dataTable').innerHTML = html;
            document.getElementById('input-page').value = Page;

            // 點擊切換直排顏色
            let elements = document.querySelectorAll('.th-parent');
            elements.forEach(function(element) {
                element.addEventListener('click', function(e) {
                    let className = this.className;
                    if(className == 'th-parent') setColumnEnable(this, 'th', false); // 忽略欄位
                    if(className == 'th-parent th-ignore') setColumnEnable(this, 'th', true); // 啟用欄位
                });
            });
        }

        // 渲染設定
        function renderSetting() {
            document.getElementById('setting_column_table').innerHTML = '';
            let names = Data.export.name;
            let types = Data.export.type;
            let html = '';
            names.forEach(function(value, index) {
                let type = types[index];
                html = `
                    <tr>

                        <td class="text-center align-middle">
                            <div class="text-center align-middle">
                                <input class="form-check-input" style="width: 20px; height: 20px;" type="checkbox" value="1" data-col="${index}" id="isUsed_${index}" onChange="setColumnEnable(this, 'checkbox', this.checked)" checked>
                            </div>
                        </td>
                        <td class="text-center align-middle">${index + 1}</td>
                        <td>
                            <div class="input-group" id="setting_column_name_${index}">
                                <input type="text" class="form-control" placeholder="Column Name" data-col="${index}" onChange="setColumnName(this);" value="${value}">
                            </div>
                        </td>
                        <td>
                            <select class="form-select" id="setting_column_type_${index}">
                                <option value="text" checked>Text</option>
                                <option value="int">Int</option>
                                <option value="decimal">Decimal</option>
                                <option value="datatime">Datatime</option>
                            </select>
                        </td>
                        <td>
                            <div class="input-group" id="setting_column_sv_${index}">
                                <input type="text" class="form-control" placeholder="Set Value">
                            </div>
                        </td>
                        <td>
                            <div class="input-group" id="setting_column_re_${index}">
                                <input type="text" class="form-control" placeholder="Regular Expression">
                            </div>
                        </td>
                    </tr>`;
                document.getElementById('setting_column_table').insertAdjacentHTML('beforeend', html);
                document.getElementById(`setting_column_type_${index}`).value = type;
            });
        }


        // ====================================================================================================================================
        // Export Button
        // 輸出
        function exportTable() {
            //document.getElementById('btn-export').disabled = true;

            let csvFile = document.getElementById("csvFile");
            if( Object.keys(Data).length === 0 ) return;
            let data = Data;
            let example = data.export.example;
            let ignore = data.export.ignore_column;

            let column_type = data.export.type;
            let column_name = data.export.name;

            console.log(Data);
            return;

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
                //"Content-Type": "multipart/form-data",
                "Accept": "application/json",
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }

            // 分割檔案以上傳
            let file = csvFile.files[0];
            let file_name = file.name.replace('.csv', '');
            let chunkSize = 6000000; // 字節，約5MB
            let count = 0;
            let num = 0;
            let progress = 0; // 進度條
            let total = (file.size / chunkSize).toFixed(0); // 總分割塊數
            if( file.size % chunkSize !== 0 ) total++; // 最後一塊未滿
            for (let start = 0; start <= file.size; start += chunkSize) {
                let chunk = file.slice(start, start + chunkSize + 1);
                let formData = new FormData();

                // 發送json & file
                formData.append('name', file_name); // 檔名
                formData.append('file', chunk); // 檔案塊
                formData.append('num', num); // 第N個分割檔

                fetch('upload', {
                    method: 'post',
                    headers: headers,
                    body: formData
                })
                .then(response => response.json())
                .then((data) => {
                    console.log(data);
                    if( data.status === 'success' ) count++; // 下載完成的分割檔計數器


                    // 進度條
                    progress = (count / total).toFixed(0) * 100;
                    let element = document.getElementById('progress_bar');
                    element.innerHTML = `${progress} %`;
                    element.style.width = `${progress}%`;
                    element.classList.add('bg-warning');
                    element.classList.remove('bg-success');

                    // 所有分割檔下載完成call後端處理
                    if( count === total ) {

                        let headers = {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        };
                        let body = {
                            'name' : file_name, // table name
                            'column' : column_name, // columns name
                            'type' : column_type, // colums type
                            'ignore' : ignore, // ignore columns
                            'count' : count // 檔案分割數
                        };

                        fetch(`upload_finished`, {
                            method: 'post',
                            headers: headers,
                            body: JSON.stringify(body)
                        })
                        .then(response => response.json())
                        .then((data) => {
                            console.log(data);
                            if( progress == 100 ) {
                                element.classList.remove('bg-warning');
                                element.classList.add('bg-success');
                            }
                        })
                        .catch(error => console.error(error));
                    }
                })
                .catch(error => console.error(error));
                num++;
            }

        }


        // ====================================================================================================================================
        // Page Control
        // 上下一頁
        function turnPage(page) {
            if( Object.keys(Data).length === 0 ) return;
            if( (Page + page) > 0 && (Page + page) <= Data.original.body.length ) Page += page;
            pageAction(Page);
        }

        // 指定頁數
        function specifiedPage(page) {
            if( Object.keys(Data).length === 0 ) {
                document.getElementById('input-page').value = 0;
                return;
            }
            page = Number(page);
            if( page > 0 && page <= Data.original.body.length ) Page = page;
            if( page < 0 ) Page = 1;
            if( page > Data.original.body.length ) Page = Data.original.body.length;
            document.getElementById('input-page').value = Page;
            pageAction(Page);
        }

        // 翻頁 action
        function pageAction(page) {
            renderTable(page);
        }


        // ====================================================================================================================================
        // Column Setting Control
        // 欄位啟用與否 (for checkbox & datatable th)
        function setColumnEnable(e, e_type, enable) {
            let className = e.className;
            let i = e.dataset.col;
            let child = document.querySelectorAll(`[data-col='${i}']`);
            if(e_type === 'th') {
                // 忽略
                if(enable == false) {
                    child.forEach(function(c) {
                        c.classList.add("th-ignore");
                    });
                    Data.export.ignore_column[i] = 0;
                    document.getElementById(`isUsed_${i}`).checked = false;
                }
                // 啟用
                if(enable === true) {
                    child.forEach(function(c) {
                        c.classList.remove("th-ignore");
                    });
                    Data.export.ignore_column[i] = 1;
                    document.getElementById(`isUsed_${i}`).checked = true;
                }
                console.log(Data);
            }

            if(e_type === 'checkbox') {
                // 忽略
                if(enable == false) {
                    child.forEach(function(c) {
                        c.classList.add("th-ignore");
                    });
                    Data.export.ignore_column[i] = 0;
                }
                // 啟用
                if(enable === true) {
                    child.forEach(function(c) {
                        c.classList.remove("th-ignore");
                    });
                    Data.export.ignore_column[i] = 1;
                }
            }

        }
        // 更換欄位名字
        function setColumnName(e) {
            let name = e.value;
            if(name == '') name = 'column';
            let i = e.dataset.col;
            document.getElementById(`th-parent-${i}`).innerHTML = name; // table column name change
            Data.export.name[i] = name; // export json name change
        }


    </script>
@endsection