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
        #dataTable .row-ignore {
            background-color: #000000 ;
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
        <button type="button" class="btn btn-sm btn-outline-dark page_control" onclick="turnPage(-1);">Previous</button>
        <input type="text" id="input-page" class="btn btn-sm col-1 page_control" style="color: #212529; border-color: #212529; cursor: text" onchange="specifiedPage(this.value)" placeholder="Page"/>
        <button type="button" class="btn btn-sm btn-outline-dark page_control" onclick="turnPage(1);">Next</button>
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
                <h3 style="margin: 10px">檢視設定</h3>
                <div class="row">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" style="visibility: hidden">
                            <label class="form-check-label" for="recordPerPage">
                                每頁
                                <select class="form-select-sm" id="setting_table_record_per_page">
                                    <option value="25" checked>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                筆資料
                            </label>
                        </div>
                    </div>
                </div>
                <hr>
                <h3 style="margin: 10px">上傳設定</h3>
                <div class="form-group row">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" style="visibility:hidden">
                            <label class="col-form-label">Table Name</label>
                            <input class="col-xs-2" type="text" onchange="setTableName(this);" id="customTableName" placeholder="請先上傳檔案" readonly="readonly">
                            <img class="img-loading" id="get_tables_name-loading" src={{ asset('images/loading.gif') }} style="width: 32px; height: 32px; display: none;">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="addToExistTable">
                            <label class="form-check-label" for="addToExistTable">
                                匯入到 Table&nbsp;
                                <select class="form-select-sm" id="addToExistTable_name">
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
                            <input class="form-check-input" type="checkbox" value="" id="ignore_start_lines_check">
                            <label class="form-check-label">
                                省略前&nbsp;<input class="col-xs-2" type="number" value="0" name="ignore_start_lines" id="ignore_start_lines" style="width:20%" onchange="setIgnoreLinesChecked('start');">&nbsp;行
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="ignore_end_lines_check">
                            <label class="form-check-label">
                                省略後&nbsp;<input class="col-xs-2" type="number" value="0" name="ignore_end_lines" id="ignore_end_lines" style="width:20%" onchange="setIgnoreLinesChecked('end');">&nbsp;行
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal" onClick="changeTableSetting();">OK</button>
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
                                <th scope="col">{Parameter Name}</th>
                                <th scope="col">Column Name</th>
                                <th scope="col">Data Type</th>
                                <th scope="col">
                                    Set Value
                                    <svg xmlns="http://www.w3.org/2000/svg" class="question-icon" data-toggle="tooltip" data-placement="top" title="Tooltip on top" width="16" height="16" fill="currentColor" class="bi bi-question-circle" viewBox="0 0 16 16">
                                        <title>使用 `{}` 來代入變數欄位。EX: `concat({col1}*100, '%')`</title>
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
        class ColumnSettingInfo {
            index = 0;
            get index() {
                return this.index;
            }

            addindex(val) {
                typeof val === 'undefined' ? this.index = 0 : this.index += val;
            }
        }

        var Data = {};
        var Page = 1;
        var File = '';
        var Column_setting_info = new ColumnSettingInfo();

        // ====================================================================================================================================
        // file button
        // 切換檔案
        function fileChange() {
            // 重置進度條
            progree = document.getElementById('progress_bar');
            progree.innerHTML = `0 %`;
            progree.style.width = `0%`;

            Column_setting_info.addindex(); // 欄位設定重置

            document.getElementById('customTableName').readOnly = true; // 禁用 checkbox of table name
            document.getElementById('customTableName').value = ''; // 重置 table name
            
            // 禁用翻頁
            for (i = 0; i < document.getElementsByClassName('page_control').length; i++) { 
                document.getElementsByClassName('page_control')[i].disabled = true;
            }

            // 啟/禁用 import/export
            document.getElementById('btn-import').disabled = false;
            document.getElementById('btn-export').disabled = true;
            File = '';
            document.getElementById('btn-import').disabled = false;
            document.getElementById('btn-export').disabled = true;
            document.getElementById('dataTable').innerHTML = ''; // 重置表格
            Page = 1; // 重置當前頁數
            document.getElementById('input-page').value = ''; // 重置輸入頁數
        }
        

        // ====================================================================================================================================
        // Import Button
        // 建立view表格
        function createTable() {
            // 啟/禁用 import/export
            document.getElementById('btn-import').disabled = true;
            document.getElementById('btn-export').disabled = false;

            // 啟用翻頁
            for (i = 0; i < document.getElementsByClassName('page_control').length; i++) { 
                document.getElementsByClassName('page_control')[i].disabled = false;
            }

            let csvFile = document.getElementById("csvFile");
            if( csvFile.files.length === 0 || File === csvFile.files[0].name ) return;
            else File = csvFile.files[0].name;

            let reader = new FileReader();
            let f = csvFile.files[0];
            reader.onload = function(e) {
                Data = arrayToTable(e.target.result);
                console.log(Data);

                Page = 1;
                renderSetting();
                renderTable(Page, getSettingParameters());
    
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

        // 陣列資料
        function arrayToTable(result) {
            let array = csvToArray(result); // csv to array
            let data = {};

            let row_column_type_line = 1; // 欄位型別判斷依據

            let null_row_flag = false; // 判斷每個row是否有null值 (自動判斷型別依據)
            let null_doing_flag = true; // 是否需繼續做


            // 配置html
            array.forEach(function(row, index) {
                // 首行為欄位名稱
                if( index == 0 ) {
                    data.original = {};
                    data.original.body = [];
                    data.export = {};
                    data.export.ignore_column = []; // 不存入的欄位

                    let duplicates = []; // 檢查欄位名稱是否有重複
                    let duplicates_count = []; // 重複次數
                    row.forEach(function(col, i) {
                        data.export.ignore_column.push(1); // 替每個欄位上記號，預設存入欄位

                        // 檢查欄位名稱有無重複========================================
                            let index = duplicates.indexOf(col);
                            if(index !== -1) {
                                duplicates_count[index]++;
                                row[i] = `${row[i]} (${duplicates_count[index]})`;
                            }
                            else {
                                duplicates.push(col); // 檢查通過
                                duplicates_count.push(1); // 該欄位名目前出現次數
                            }
                        // end of 檢查欄位名稱有無重複=================================
                    });
                    data.original.head = row; // 放入檢查後的欄位列
                }
                else {
                    // 若資料小於欄位數補 null
                    if(row.length < data.original.head.length) {
                        toastr.error(`資料大於欄位資料數，請檢查你的csv檔案<br>錯誤列：${index + 1}`);
                        fileChange();
                        throw new Error(`資料大於欄位資料數，請檢查你的csv檔案\n錯誤列：${index + 1}`);
                    }

                    if(row.length > data.original.head.length) {
                        toastr.error(`資料小於欄位資料數，請檢查你的csv檔案<br>錯誤列：${index + 1}`);
                        fileChange();
                        throw new Error(`資料小於欄位資料數，請檢查你的csv檔案\n錯誤列：${index + 1}`);
                    }

                    null_row_flag = false;
                    data.original.body.push(row);

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

            // 判斷欄位型別
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
        function renderTable(page, option) {
            let data = Data;
            let html = '';
            let html_head = '';
            let html_body = '';
            let head = data.original.head;
            let body = data.original.body;
            
            let record_per_page = 25; // 每頁 n 筆資料
            let now_page = 1; // 第 n 頁
            let ignore_start_lines = 0; // 忽略前 n 筆資料 (不含第一行(欄位名))
            let ignore_end_lines = 0; // 忽略尾 n 筆資料
            if(typeof option !== 'undefined') {
                typeof option.record_per_page === 'undefined' ? record_per_page : record_per_page = option.record_per_page;
                typeof option.now_page === 'undefined' ? now_page : now_page = option.now_page;
                typeof option.ignore_start_lines === 'undefined' ? ignore_start_lines : ignore_start_lines = option.ignore_start_lines;
                typeof option.ignore_end_lines === 'undefined' ? ignore_end_lines : ignore_end_lines = option.ignore_end_lines;
            }

            // 總頁數
            data.pages =  parseInt(body.length / record_per_page);
            body.length % record_per_page == 0 ? data.pages : data.pages++;

            // view - column name
            head.forEach(function(col, i) {
                if(i == 0) html_head += `<th></th>`;
                if( data.export.ignore_column[i] == 1 ) html_head += `<th data-col="${i}" id="th-parent-${i}" class="th-parent">${col}</th>`;
                if( data.export.ignore_column[i] == 0 ) html_head += `<th data-col="${i}" id="th-parent-${i}" class="th-parent th-ignore">${col}</th>`;
            });
            html_head = `<thead><tr>${html_head}</tr></thead>`;

            // view - column value
            let index_start = (page - 1) * record_per_page; // 該頁數的第一筆資料之 index
            let index_end = page * record_per_page; // 該頁數最後一筆資料之 index
            let index_ignore_start_lines = ignore_start_lines - 1; // 忽略前幾行之 index
            let index_ignore_end_lines = body.length - ignore_end_lines; // 忽略後幾行之 index

            // 欄位css (是否不存入 row & 是否忽略 row)
            for(let i = index_start; i < index_end; i++) {
                if(i > body.length - 1) break;
                let html_temp = `<td>${i + 1}</td>`;
                body[i].forEach(function(col, index) {
                    if( ignore_start_lines > 0 && i <= index_ignore_start_lines ) {
                        html_temp += `<td data-col="${index}" class="th-child-${index} row-ignore">${col}</td>`;
                        return;
                    }
                    if( ignore_end_lines > 0 && i >= index_ignore_end_lines ) {
                        html_temp += `<td data-col="${index}" class="th-child-${index} row-ignore">${col}</td>`;
                        return;
                    }

                    if( data.export.ignore_column[index] == 1 ) html_temp += `<td data-col="${index}" class="th-child-${index}">${col}</td>`;
                    if( data.export.ignore_column[index] == 0 ) html_temp += `<td data-col="${index}" class="th-child-${index} th-ignore">${col}</td>`;
                });
                html_body += `<tr>${html_temp}</tr>`
            }
            html_body = `<tbody><tr>${html_body}</tr></tbody>`;

            // combine
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

        // 渲染 Column Setting button
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
                        <td class="text-center align-middle">col${index + 1}</td>
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
                                <input type="text" class="form-control setting_column_sv" placeholder="Set Value">
                            </div>
                        </td>
                    </tr>`;
                document.getElementById('setting_column_table').insertAdjacentHTML('beforeend', html);
                document.getElementById(`setting_column_type_${index}`).value = type;
                Column_setting_info.addindex(1);
            });

            html = `
                <tr id="tr_setting_add_column">
                    <td class="text-center align-middle">
                        <div class="text-center align-middle">
                            <input type="button" name="setting_add_column" id="setting_add_column" value="➕" title="add column" onclick="addNewColumn();">
                        </div>
                    </td>
                </tr>`
            document.getElementById('setting_column_table').insertAdjacentHTML('beforeend', html);

            
            document.getElementById('customTableName').readOnly = false;
            document.getElementById('customTableName').value = csvFile.files[0].name.replace('.csv', '');
        }


        // ====================================================================================================================================
        // Export Button
        // 輸出
        function exportTable() {
            //document.getElementById('btn-export').disabled = true;
            let option = getSettingParameters();
            let insert_to_exist_table = document.getElementById('addToExistTable').checked;
            let insert_to_exist_table_name = document.getElementById('addToExistTable_name').value;
            let ignore_start_lines = parseInt(option.ignore_start_lines);
            let ignore_end_lines = parseInt(option.ignore_end_lines);

            // 插入 table 檢查
            if( insert_to_exist_table == true && insert_to_exist_table_name == '' ) {
                toastr.error('請選擇欲插入 table');
                return;
            }

            // 忽略行數檢查
            if( (ignore_start_lines + ignore_end_lines) > Data.original.body.length ) {
                toastr.error(`忽略行數超過總行數。<br>忽略首行：${ignore_start_lines} | 忽略尾行：${ignore_end_lines}<br>合計：${ignore_start_lines + ignore_end_lines} | 總行數：${Data.original.body.length}`);
                return;
            }

            let csvFile = document.getElementById("csvFile");
            if( Object.keys(Data).length === 0 ) return;
            let data = Data;
            let example = data.export.example;
            let ignore = data.export.ignore_column;

            let column_type = data.export.type;
            let column_name = data.export.name;

            console.log(Data);

            let headers = {
                //"Content-Type": "multipart/form-data",
                "Accept": "application/json",
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }

            let file = csvFile.files[0];
            let table_name = document.getElementById('customTableName').value;
            // 轉成base64並分割上傳 (轉base64以避免utf8字元編碼被切割問題)
            let reader = new FileReader();
            reader.onload = function(e) {
                let base64data = this.result; // base64
                let chunkSize = 5 * 1024 * 1024; // 分割成5MB一組
                let count = 0; // 發送成功chunk數
                let num = 0; // 發送第 n 個chunk數
                let progress = 0; // 進度條
                let total = Math.ceil(base64data.length / chunkSize) + 1; // 總分割塊數 (+1為最後檔案處理的部分，給進度條顯示用)
                for (let start = 0; start <= base64data.length; start += chunkSize) {
                    let chunk = base64data.slice(start, start + chunkSize);

                    // 發送json & file
                    let formData = new FormData();
                    formData.append('name', table_name); // 檔名
                    formData.append('base64data', chunk); // chunk
                    formData.append('num', num); // 第N個chunk
    
                    fetch('upload', {
                        method: 'post',
                        headers: headers,
                        body: formData
                    })
                    .then(response => response.json())
                    .then((data) => {
                        console.log(data);
                        if( data.status === 'success' ) count++; // 上傳完成的分割計數器

                        // 進度條
                        progress = (count / total).toFixed(0) * 100;
                        let element = document.getElementById('progress_bar');
                        element.innerHTML = `${progress} %`;
                        element.style.width = `${progress}%`;
                        element.classList.add('bg-warning');
                        element.classList.remove('bg-success');

                        // 所有分割檔下載完成call後端處理
                        if( count == (total - 1) ) {
                            

                            let set_column_value = [];
                            for(i = 0; i < document.getElementsByClassName('setting_column_sv').length; i++) {
                                set_column_value.push(document.getElementsByClassName('setting_column_sv')[i].value);
                            }
                            console.log(set_column_value);
                            let headers = {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            };
                            let body = {
                                'name' : table_name, // table name
                                'column' : column_name, // columns name
                                'type' : column_type, // colums type
                                'ignore' : ignore, // ignore columns
                                'count' : count, // 檔案分割數
                                'ignore_start_lines' : ignore_start_lines, // 忽略前 n 行
                                'ignore_end_lines' : ignore_end_lines, // 忽略後 n 行
                                'insert_to_exist_table' : insert_to_exist_table, // 是否插入已存在的 table
                                'insert_to_exist_table_name' : insert_to_exist_table_name, // 插入已存在的 table name
                                'set_column_value' : set_column_value // 客製化欄位設值
                            };
    
                            fetch(`upload_finished`, {
                                method: 'post',
                                headers: headers,
                                body: JSON.stringify(body)
                            })
                            .then(response => response.json())
                            .then((data) => {
                                console.log(data);
                                if(data.status === 'success') {
                                    if( progress == 100 ) {
                                        element.classList.remove('bg-warning');
                                        element.classList.add('bg-success');
                                    }

                                    if( insert_to_exist_table == true ) {
                                        toastr.success(`insert success!<br>table: "${insert_to_exist_table_name}"`);
                                    }

                                    if( insert_to_exist_table == false ) {
                                        toastr.success(`export success!<br>table: "${table_name}"`);
                                        getTablesName().then((data) => {
                                            let tables_name = data;
                                            console.log(tables_name);
                                            if( tables_name !== false ) {
                                                let element = document.getElementById('addToExistTable_name');
                                                let html = '';

                                                tables_name.forEach(function(value) {
                                                    html += `<option value="${value.table_name}">${value.table_name}</option>`
                                                });
                                                element.innerHTML = html;
                                            }
                                        });

                                    }
                                } else {
                                    toastr.error(data.message);
                                }

                            })
                            .catch(error => console.error(error));
                        }
                    })
                    .catch(error => console.error(error));
                    num++;
                }
            };
            reader.readAsDataURL(file); // 以base64讀取
        }


        // ====================================================================================================================================
        // Page Control
        // 上下一頁
        function turnPage(page) {
            if( Object.keys(Data).length === 0 ) return;
            if( (Page + page) > 0 && (Page + page) <= Data.pages ) Page += page;
            pageAction(Page);
        }

        // 指定頁數
        function specifiedPage(page) {
            if( Object.keys(Data).length === 0 ) {
                document.getElementById('input-page').value = 0;
                return;
            }
            page = Number(page);
            if( page > 0 && page <= Data.pages ) Page = page;
            if( page < 0 ) Page = 1;
            if( page > Data.pages ) Page = Data.pages;
            document.getElementById('input-page').value = Page;
            pageAction(Page);
        }

        // 翻頁 action
        function pageAction(page) {
            renderTable(page, getSettingParameters());
        }

        // ====================================================================================================================================
        // Table Setting Control
        // 確認 Table Setting 是否有變更設定
        function changeTableSetting() {
            if(File == '') {
                Page = 1;
                renderSetting();
                renderTable(Page, getSettingParameters());
            }
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

        // 更改表名
        function setTableName(e) {
            getTablesName().then((data) => {
                let tables_name = data;
                document.getElementById('get_tables_name-loading').style.display = 'none';
                if( tables_name !== false && tables_name.find(v => v.TABLE_NAME === e.value)) {
                    toastr.error(`${e.value} 已存在<br>請輸入其他名稱`);
                    e.value = csvFile.files[0].name.replace('.csv', '');
                }
            });
        }

        // 更換欄位名字
        function setColumnName(e) {
            let name = e.value;
            let i = e.dataset.col;
            if(name == '') {
                name = `col${Number(i) + 1}`;
                e.value = name;
                toastr.error("column name can't not be null!<br>");
            }
            document.getElementById(`th-parent-${i}`).innerHTML = name; // table column name change
            Data.export.name[i] = name; // export json name change
        }

        // 取得 Setting 參數
        function getSettingParameters() {
            let record_per_page = document.getElementById('setting_table_record_per_page').value;
            let ignore_start_lines = document.getElementById('ignore_start_lines').value;
            let ignore_end_lines = document.getElementById('ignore_end_lines').value;
            let ignore_start_lines_check = document.getElementById('ignore_start_lines_check').checked;
            let ignore_end_lines_check = document.getElementById('ignore_end_lines_check').checked;

            if(ignore_start_lines_check == false) ignore_start_lines = 0;
            if(ignore_end_lines_check == false) ignore_end_lines = 0;

            let option = {
                record_per_page : record_per_page,
                ignore_start_lines : ignore_start_lines,
                ignore_end_lines : ignore_end_lines,
            }

            return option;
        }

        // setting column & data table column 互動
        function setIgnoreLinesChecked(name) {
            let input = document.getElementById('ignore_start_lines');
            let checkbox = document.getElementById('ignore_start_lines_check');
            if(name === 'start') {
                input = document.getElementById('ignore_start_lines');
                checkbox = document.getElementById('ignore_start_lines_check');
            }

            if(name === 'end') {
                input = document.getElementById('ignore_end_lines');
                checkbox = document.getElementById('ignore_end_lines_check');
            }

            if(parseInt(input.value) <= 0) {
                input.value = 0;
                checkbox.checked = false;
            }

            if(parseInt(input.value) > 0) {
                checkbox.checked = true;
            }
        }

        // 新增 column
        function addNewColumn() {
            let index = Column_setting_info.index;
            let html = `
                    <tr id="tr_setting_delete_column_${index}">
                        <td class="text-center align-middle">
                            <div class="text-center align-middle">
                                <input type="button" class="setting_delete_column" data-del="${index}" value="➖" title="delete column" onclick="removeNewColumn(this.dataset.del);">
                            </div>
                        </td>
                        <td class="text-center align-middle">col${index + 1}</td>
                        <td>
                            <div class="input-group" id="setting_column_name_${index}">
                                <input type="text" class="form-control" placeholder="Column Name" data-col="${index}" value="col${index + 1}">
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
                                <input type="text" class="form-control setting_column_sv" placeholder="Set Value" value="{col1}">
                            </div>
                        </td>
                    </tr>`;
            document.getElementById('tr_setting_add_column').insertAdjacentHTML('beforebegin', html);
            Column_setting_info.addindex(1);
        }

        // 移除新增的 column
        function removeNewColumn(index) {
            let element = document.getElementById(`tr_setting_delete_column_${index}`);
            element.parentNode.removeChild(element);
        }

        // 判斷 table name 是否已存在資料庫
        function getTablesName() {
            return new Promise(function(resolve, reject) {
                document.getElementById('get_tables_name-loading').style.display = 'inline';
                let headers = {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                };
                fetch('tables_name', {
                    method : 'get',
                    headers : headers
                })
                .then(response => response.json())
                .then((data) => {
                    if(data.status === 'success') {
                        resolve(data.data);
                    } else {
                        reject(data.message);
                    }
                })
                .catch(error => console.error(error));
            });
        }

        // 取得 table columns 資訊
        function getTableColumnsInfo() {
            if( document.getElementById('addToExistTable').checked === true ) {
                return new Promise(function(resolve, reject) {
                    let table_name = document.getElementById('addToExistTable_name').value;
                    let headers = {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    };
                    fetch('table_columns_info?' + new URLSearchParams({
                        "table_name" : table_name,
                    }), {
                        method : 'get',
                        headers : headers
                    })
                    .then(response => response.json())
                    .then((data) => {
                        console.log(data);
                        if(data.status === 'success') {
                            resolve(data.data);
                        } else {
                            reject(data.message);
                        }
                    })
                    .catch(error => console.error(error));                
                });
            }
        }
    </script>
@endsection