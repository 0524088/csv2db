-- 建立使用者資料表
CREATE TABLE IF NOT EXISTS `users` (
	id int AUTO_INCREMENT PRIMARY KEY,
    account varchar(80),
    password varchar(80),
    token varchar(80),
    created_at datetime,
    updated_at datetime
)