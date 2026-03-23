
# coachtech 勤怠管理アプリ 


## 環境構築 

**Dockerビルド** 

- git clone git@github.com:mako236630/coachtech-attendance-management.git
- cd coachtech-attendance-management 
- docker-compose up -d --build 

**laravel 環境構築** 

- docker-compose exec php bash
- composer install
- cp .env.example .env  
.env は、DB接続部分を修正してください。 
また、Mailtrapの設定をしてください。  
- php artisan key:generate 
- php artisan migrate 
- php artisan db:seed 
  
## 開発環境 
 
- 会員登録画面: http://localhost/register 
- ログイン画面(一般ユーザー): http://localhost/login  
一般ユーザーでの機能確認には、以下のテストアカウントをご利用ください。  
メールアドレス: test@example.com  
パスワード: password  

- ログイン画面(管理者): http://localhost/admin/login  
 管理者での機能確認には、以下のテストアカウントをご利用ください。  
 メールアドレス: admin@example.com  
 パスワード: adminpass  
 
 - phpMyAdmin: http://localhost:8080/  
 
## 使用技術  
 - Docker 28.4.0
 - nginx 1.21.1
 - MySQL 8.0.26
 - php 8.1.34
 - laravel 8.83.8  
 
## ER図  
 - rests(休憩データ): 一日の中で複数回の休憩を可能にする為、休憩データを独立させattendances(勤怠データ)と1対多で紐付けています。
 - 打刻時刻と修正時刻について: 打刻時刻と修正申請中の時刻を別のカラムで管理し、管理者による承認後のデータ更新を安全に行えるようにしています。
 - 申請ステータスの管理: attendances(勤怠データ)にstatusカラムを設け、「0:未申請」「1:承認待ち」「2:承認済み」の状態を管理し管理者が確認・承認したデータのみを正として扱えるようにしました。  
 
![ER図](er.png)


