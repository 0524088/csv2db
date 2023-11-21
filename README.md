# csv2db
將 csv 檔透過 web 方式操作即可直接匯入資料庫，可在前端先檢視並且設定欄位名資料型別等，即可在資料庫建立表並匯入

# 環境
1. 匯入 `建立使用者資料表.sql` 並到 `/register?account={account}&password={password}` 建立使用者<br>
2. 修改 MySQL 的 `my.ini` 檔，搜尋 `secure-file-priv` 修改 `secure-file-priv="{path}/test_file"`

# 系統 DEMO
1. 選擇單個 csv 檔
![image](https://github.com/0524088/csv2db/assets/144317928/719f5c66-2601-472a-9f1e-5dcc7ea79fb3)

2. 預設抓取 csv 檔名作為資料表名稱，亦可選擇匯入到已存在的資料表；可選擇要忽略的頭/尾 N 行資料
![image](https://github.com/0524088/csv2db/assets/144317928/a2bbfa69-6922-4415-97b0-9538959c4105)
![image](https://github.com/0524088/csv2db/assets/144317928/f4e5ea63-f5d3-4382-b9ff-5792899080fd)

3. 預設抓取第一行資料作為欄位名稱
   - 如欄位名稱重複則會自動遞增值
   - 欄位型別會遍歷資料列直到找到齊全(無空值)的一行做為資料型別判斷依據
![image](https://github.com/0524088/csv2db/assets/144317928/f27514a6-942b-4149-a2d1-cfddc4c3aca1)

4. 可勾選要忽略的欄位，亦可直接點擊表格切換是否匯入該欄
![image](https://github.com/0524088/csv2db/assets/144317928/e56bdd66-3965-4a92-8246-1a456179ab43)

5. 可自行選擇型別，亦可客製化欄位資料(直接輸入 sql)
   - 新增欄位功能暫未完成
![image](https://github.com/0524088/csv2db/assets/144317928/73e8a8a6-ef08-4115-93fc-46b0601ee492)
![image](https://github.com/0524088/csv2db/assets/144317928/6119df4e-70be-44c8-a593-a8e7af55e272)







