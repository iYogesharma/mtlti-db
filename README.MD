# MultiDB #

### What is this repository for? ###

* This Repo is created to make creating one main database and multiple client specific database easily. 
* It will automatically resolve db connection names using some properties defined in
YS\MultiDB\Models\Model Class.

* Model::$cachekey key to search client db name in cache. if  set it will auto configure database connection based on this key value from cache. If not itn will connect to default database

* Model::$cronKey Similar to $cacheKey you can set this key from crons jobs to automatically resolve database conection

* Version 1.0

### How do I get set up? ###

* Installing 

```php

    composer require iyogesharma/multi-db

    php artisan vendor:publish --tag=multidb:migrations

    php artisan vendor:publish --tag=multidb:models

    //optional
    
    php artisan vendor:publish --tag=multidb:config

```


## For PgSql

* instead of default  Illuminate\Database\Migrations\Migration  extend  YS\MultiDB\Migration
* This class contains a static property called $schema which helps in identifying schema name for running migrations with dynamic schema names

### For Example 
    ```php 

            <?php

        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;
        use YS\MultiDB\Migration;

        return new class extends Migration
        {
            /**
             * Run the migrations.
             */
            public function up(): void
            {
                Schema::create(static::$schema.'.clients', function (Blueprint $table) {
                    $table->id();
                    $table->string('code');
                    $table->string('name');
                    $table->string('created_by');
                    $table->string('modified_by')->nullable();
                    $table->boolean('active')->default(1);
                    $table->timestamps();
                });
            }

            /**
             * Reverse the migrations.
             */
            public function down(): void
            {
                Schema::dropIfExists('clients');
            }
        };

    ```

* In your Models instead of default model class extend YS\MultiDB\Models\Model Class
* This class contains a static propert called $inMaindb with default value as false.
You can set this value to true if your model belongs to main database not client specific database

```php
  protected static $inMainDb = false;
```


## To run client database/schema specific migration 

* create a directory called newdatabase inside app directory 
* you can customize name of this directory just publish config and update  value of key new_migrations_path
* Place all the migration related to client specific database inside this folder
* Run ``` php artisan new:migrations ```
* Run ``` php artisan new:migrations_rollback ``` to rollback migrations
* To create new migration inside app/newdatabase folder for client specific database use 
* ``` php artisan make:new-migration {name of migration} ```
* In order to run seeders for client specific database you can create seeders directory inside app/newdatabase folder and pass --seed option along with php artisan new:migrations command to run seeders inside this directory
* To run only seeders you can run ```php artisan new:seed ```
* to run a specific seeder inside this folder run
* ``` php artisan new:seed --path=App\\Newdatabase\\Seeders\\TESTSeeder    ```


## To Check How TO Create New DB Or Schema 
``` @see  src/Controllers/ClientController ```


### Who do I talk to? ###

* [Yogesh Sharma](https://github.com/iYogesharma)
