# mitani-mockcase-2
## 環境構築
**Dockerビルド**
1. `git clone git@github.com:koto-101/mitani-mockcase-2.git`
2. DockerDesktopアプリを立ち上げる
3. `docker-compose up -d --build`

**Laravel環境構築**
1. PHPコンテナに入る
`docker-compose exec php bash`
2. パッケージインストール
`composer install`
3. ディレクトリを移動
`cd src/`
4. .env ファイル作成
```bash
cp .env.example .env
```
5. .env に以下のDB設定を記述
``` text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

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

7. シーディングの実行
``` bash
php artisan db:seed
```

## ER図
![ER図](./er.png)

## URL
- 開発環境：http://xxxxxx
- phpMyAdmin:：http://xxxxxx