```
php artisan make:migration create_user_table
php artisan make:seed UserTableSeeder
php artisan db:seed


php artisan queue:table
php artisan queue:failed-table

php artisan queue:work --queue=high,low
php artisan queue:restart

php artisan queue:work --queue=high,low --daemon


docker linux ps 命令
apt-get update && apt-get install procps
```