# csv2db
將 csv 檔透過 web 方式操作即可直接匯入資料庫，可在前端先檢視並且設定欄位名資料型別等，即可在資料庫建立表並匯入

## 環境
建立 users 資料庫並到 /register 建立使用者<br>
修改 MySQL 的 `my.ini` 檔，搜尋 `secure-file-priv` 修改 `secure-file-priv="{path}/test_file"`
