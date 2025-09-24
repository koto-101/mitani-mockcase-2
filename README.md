# mitani-mockcase-2
## 環境構築
**Dockerビルド**
1. リポジトリをクローン
 `git clone git@github.com:koto-101/mitani-mockcase-2.git`
2. ディレクトリを移動
`cd mitani-mockcase-2`
3. DockerDesktopアプリを立ち上げる
`docker-compose up -d --build`

**Laravel環境構築**
1. PHPコンテナに入る
`docker-compose exec php bash`
2. パッケージインストール
`composer install`
3. ディレクトリを移動
`cd src/`
4. .env ファイル作成
`cp .env.example .env`
5. .env に以下のDB設定を記述
``` text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

```
MAIL_FROM_ADDRESS=no-reply@example.com
``` text


```
6. アプリケーションキーの作成
``` bash
php artisan key:generate
```
7. 設定キャッシュ（.env変更時は都度推奨） 
``` bash 
php artisan config:cache
```
8. マイグレーションの実行
``` bash
php artisan migrate
```

9. シーディングの実行
``` bash
php artisan db:seed
```

## テーブル仕様
### usersテーブル
| カラム名                | 型           | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
| ------------------- | ----------- | ----------- | ---------- | -------- | ----------- |
| id                  | id          | ○           |            | ○        |             |
| name                | string      |             |            | ○        |             |
| email               | string      |             | ○          | ○        |             |
| password            | string      |             |            | ○        |             |
| is\_admin           | boolean     |             |            | ○        |             |
| email\_verified\_at | timestamp   |             |            |          |             |
| remember\_token     | string(100) |             |            |          |             |
| created\_at         | timestamp   |             |            |          |             |
| updated\_at         | timestamp   |             |            |          |             |

### attendances テーブル
| カラム名         | 型               | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
| ------------ | --------------- | ----------- | ---------- | -------- | ----------- |
| id           | id              | ○           |            | ○        |             |
| user\_id     | unsigned bigint |             |            | ○        | users(id)   |
| date         | date            |             |            | ○        |             |
| clock\_in    | datetime        |             |            |          |             |
| clock\_out   | datetime        |             |            |          |             |
| note         | text            |             |            |          |             |
| status       | string          |             |            |          |             |
| recorded\_at | datetime        |             |            |          |             |
| created\_at  | timestamp       |             |            |          |             |
| updated\_at  | timestamp       |             |            |          |             |

### stamp_correction_requests テーブル
| カラム名           | 型               | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY                 |
| -------------- | --------------- | ----------- | ---------- | -------- | --------------------------- |
| id             | id              | ○           |            | ○        |                             |
| attendance\_id | unsigned bigint |             |            | ○        | attendances(id)             |
| user\_id       | unsigned bigint |             |            | ○        | users(id)                   |
| status         | enum            |             |            | ○        | pending, approved, rejected |
| reason         | text            |             |            | ○        |                             |
| clock\_in      | time            |             |            | ○        |                             |
| clock\_out     | time            |             |            |          |                             |
| requested\_at  | timestamp       |             |            | ○        |                             |
| approved\_at   | timestamp       |             |            | ○        |                             |
| created\_at    | timestamp       |             |            |          |                             |
| updated\_at    | timestamp       |             |            |          |                             |

### attendance_logs テーブル
| カラム名        | 型               | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY                                  |
| ----------- | --------------- | ----------- | ---------- | -------- | -------------------------------------------- |
| id          | id              | ○           |            | ○        |                                              |
| user\_id    | unsigned bigint |             |            | ○        | users(id)                                    |
| type        | enum            |             |            | ○        | clock\_in, clock\_out, break\_in, break\_out |
| logged\_at  | datetime        |             |            | ○        |                                              |
| created\_at | timestamp       |             |            |          |                                              |
| updated\_at | timestamp       |             |            |          |                                              |

### correction_break_logs テーブル
| カラム名                           | 型               | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY                     |
| ------------------------------ | --------------- | ----------- | ---------- | -------- | ------------------------------- |
| id                             | id              | ○           |            | ○        |                                 |
| stamp\_correction\_request\_id | unsigned bigint |             |            | ○        | stamp\_correction\_requests(id) |
| start\_time                    | time            |             |            |          |                                 |
| end\_time                      | time            |             |            |          |                                 |
| created\_at                    | timestamp       |             |            |          |                                 |
| updated\_at                    | timestamp       |             |            |          |                                 |

### break_logs テーブル
| カラム名           | 型               | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY     |
| -------------- | --------------- | ----------- | ---------- | -------- | --------------- |
| id             | id              | ○           |            | ○        |                 |
| attendance\_id | unsigned bigint |             |            | ○        | attendances(id) |
| start\_time    | datetime        |             |            | ○        |                 |
| end\_time      | datetime        |             |            |          |                 |
| created\_at    | timestamp       |             |            |          |                 |
| updated\_at    | timestamp       |             |            |          |                 |


## ER図
![ER図](./er.png)

## URL
- 開発環境：http://xxxxxx
- phpMyAdmin:：http://xxxxxx