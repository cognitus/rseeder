# RSeeder - Reverse Seeder Library

Reverse Seeder Library for Laravel

### Requirements

- RSeeder works with PHP 5.6 or above.

### Installation

```bash
$ composer require gguney/rseeder
```
Add package's service provider to your config/app.php

```php
...
        GGuney\RSeeder\RSeederServiceProvider::class,
...
```
### Usage
```bash
$ php artisan make:reverseSeeder table_name
```
If you want to get rows from a date, you can use like:
######Warning: From date will not be included.
```bash
$ php artisan make:reverseSeeder table_name --from=1990-01-22 --by=created_at
```

Also, you can ignore some columns:
```bash
$ php artisan make:reverseSeeder table_name --except=id,is_created 
```
All together:

```bash
$ php artisan make:reverseSeeder food_orders --from='2017-03-17 10:00:00' --by=created_at --except=id
```

Output:
```bash
$ FoodOrdersTableSeeder named seeder created in seeds folder.
```

### Author

Gökhan Güney - <gokhanguneygg@gmail.com><br />

### License

RSeeder is licensed under the MIT License - see the `LICENSE` file for details
