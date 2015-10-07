# Lumen generators

[![Build Status](https://travis-ci.org/webNeat/lumen-generators.svg?branch=master)](https://travis-ci.org/webNeat/lumen-generators)
[![License](https://poser.pugx.org/laravel/framework/license.svg)](http://opensource.org/licenses/MIT)

A collection of generators for [Lumen](http://lumen.laravel.com) and [Laravel 5](http://laravel.com/).

## Contents

- [Why ?](#why)

- [Installation](#installation)

- [Quick Usage](#quick-usage)

- [Detailed Usage](#detailed-usage)

	- [Model Generator](#model-generator)

	- [Migration Generator](#migration-generator)

	- [Pivot Table Generator](#pivot-table-generator) (Since version 1.1.0)

	- [Controller Generator](#controller-generator)

	- [Routes Generator](#routes-generator)

	- [Resource Generator](#resource-generator)

	- [Multiple Resources From File](#multiple-resources-from-file)

- [Testing](#testing)

- [Development Notes](#development_notes)

- [Contributing](#contributing)

## Why ?

I installed Lumen and wanted to use it to create a REST API (since this is the main usage of Lumen). But I didn't find commands which will speed up my workflow. That's why I created this package and included useful commands to build a RESTful API.

This packages was mainly built to be used with Lumen, but it should work fine with Laravel 5 too.

## Installation

Add the generators package to your composer.json by running the command:

`composer require wn/lumen-generators`

Then add the service provider in the file `app/Providers/AppServiceProvider.php`like the following:

```php
public function register()
{
    if ($this->app->environment() == 'local') {
        $this->app->register('Wn\Generators\CommandsServiceProvider');
    }
}
```

**Don't forget to include the application service provider on your `bootstrap/app.php` and to enable Eloquent and Facades if you are using Lumen**


If you run the command `php artisan list` you will see the list of added commands:

```
wn:controller               Generates RESTful controller using the RESTActions trait
wn:controller:rest-actions  Generates REST actions trait to use into controllers
wn:migration                Generates a migration to create a table with schema
wn:model                    Generates a model class for a RESTfull resource
wn:pivot-table              Generates creation migration for a pivot table
wn:resource                 Generates a model, migration, controller and routes for RESTful resource
wn:resources                Generates multiple resources from a file
wn:route                    Generates RESTful routes.
```

## Quick Usage

To generate a RESTful resource for your application (model, migration, controller and RESTful routes), you simply need to run one single command. For example: 

```
php artisan wn:resource task "name;string;required;fillable project_id;integer:unsigned;numeric;fillable,key due;date;;date" --belongs-to=project
```

will generate these files:

**app/Task.php**

```php
<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model {

	protected $fillable = ["name", "project_id"];

	protected $dates = ["due"];

	public static $rules = [
		"name" => "required",
		"project_id" => "numeric",
	];

	public function project()
	{
		return $this->belongsTo("App\Project");
	}

}

```

**app/Http/Controllers/RESTActions.php**

```php
<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;


trait RESTActions {

	protected $statusCodes = [
		'done' => 200,
		'created' => 201,
		'removed' => 204,
		'not_valid' => 400,
		'not_found' => 404,
		'conflict' => 409,
		'permissions' => 401
	];

	public function all()
	{
		$m = self::MODEL;
		return $this->respond('done', $m::all());
	}

	public function get($id)
	{
		$m = self::MODEL;
		$model = $m::find($id);
		if(is_null($model)){
			return $this->respond('not_found');
		}
		return $this->respond('done', $model);
	}

	public function add(Request $request)
	{
		$m = self::MODEL;
		$this->validate($request, $m::$rules);
		return $this->respond('created', $m::create($request->all()));
	}

	public function put(Request $request, $id)
	{
		$m = self::MODEL;
		$this->validate($request, $m::$rules);
		$model = $m::find($id);
		if(is_null($model)){
			return $this->respond('not_found');
		}
		$model->update($request->all());
		return $this->respond('done', $model);
	}

	public function remove($id)
	{
		$m = self::MODEL;
		if(is_null($m::find($id))){
			return $this->respond('not_found');
		}
		$m::destroy($id);
		return $this->respond('removed');
	}

    protected function respond($status, $data = [])
    {
    	return response()->json($data, $this->statusCodes[$status]);
    }

}
```

**app/Http/Controllers/TasksController.php**

```php
<?php namespace App\Http\Controllers;


class TasksController extends Controller {

	const MODEL = "App\Task";

	use RESTActions;

}
```

**app/Http/routes.php**

```php
// These lignes will be added
/**
 * Routes for resource task
 */
$app->get('task', 'TasksController@all');
$app->get('task/{id}', 'TasksController@get');
$app->post('task', 'TasksController@add');
$app->put('task/{id}', 'TasksController@put');
$app->delete('task/{id}', 'TasksController@remove');
```

**database/migrations/date_time_create_tasks.php**

```php
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksMigration extends Migration
{
    
    public function up()
    {
        Schema::create('tasks', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('project_id')->unsigned();
            $table->date('due');
            $table->foreign('project_id')
                ->references('id')
                ->on('projects');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('tasks');
    }
}

```

Now simply run the migration and you are ready to go.

More then that, you can generate multiple resources with only one command ! [Click here to see an example](#multiple-resources-from-file)

## Detailed Usage

### Model Generator

The `wn:model` command is used to generate a model class based on Eloquent. It has the following syntax:

```
wn:model name [--fillable=...] [--dates=...] [--has-many=...] [--has-one=...] [--belongs-to=...] [--belongs-to-many=...] [--rules=...] [--path=...]
```

- **name**: the name of the model. 

`php artisan wn:model Task` generates the following:

```php
<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model {

	protected $fillable = [];

	protected $dates = [];

	public static $rules = [
		// Validation rules
	];

	// Relationships

}
```

- **--fillable**: the mass fillable fields of the model separated with commas.

`php artisan wn:model Task --fillable=name,title` gives:

```php
//...	
	protected $fillable = ['name', 'title'];
```

- **--dates**: the date fields of the model, these will be converted automatically to `Carbon` instances on retrieval. 

`php artisan wn:model Task --dates=started_at,published_at` gives:

```php
//...	
	protected $dates = ['started_at', 'published_at'];
```

- **--path**: specifies the path where to store the model php file. This path is used to know the namespace of the model. The default value is "app".

`php artisan wn:model Task --path="app/Http/Models"` gives:

```php
<?php namespace App\Http\Models;
//...
```

- **--has-one**, **--has-many**, **--belongs-to** and **--belongs-to-many**: the relationships of the model following the syntax `relation1:model1,relation2:model2,...`. If the `model` is missing, it will be deducted from the relation's name. If the `model` is given without a namespace, it will be considered having the same namespace as the model being generated.

```
php artisan wn:model Task --has-many=accounts --belongs-to="owner:App\User" --has-one=number:Phone belongs-to-many=tags --path=tests/tmp
```
gives:

```php
//...
	public function accounts()
	{
		return $this->hasMany("Tests\Tmp\Account");
	}

	public function owner()
	{
		return $this->belongsTo("App\User");
	}

	public function number()
	{
		return $this->hasOne("Tests\Tmp\Phone");
	}

	public function tags()
	{
		return $this->belongsToMany("Tests\Tmp\Tag")->withTimestamps();
	}
```

- **--rules**: specifies the validation rules of the model's fields. The syntax is `field1=rules1 field2=rules2 ...`.

```
php artisan wn:model TestingModel --rules="name=required age=integer|min:13 email=email|unique:users,email_address"`
```
gives:

```php
// ...
	public static $rules = [
		"name" => "required",
		"age" => "integer|min:13",
		"email" => "email|unique:users,email_address",
	];
```

### Migration Generator

The `wn:migration` command is used to generate a migration to create a table with schema. It has the following syntax:

```
wn:migration table [--schema=...] [--keys=...] [--file=...]
```

- **table**: the name of the table to create.

- **--file**: The migration file name (to speicify only for testing purpose). By default the name follows the patern `date_time_create_tableName_table.php`.

- **--schema**: the schema of the table using the syntax `field1:type.arg1,ag2:modifier1:modifier2.. field2:...`. The `type` could be `text`, `string.50`, `decimal.5,2` for example. Modifiers can be `unique` or `nullable` for example. [See documentation](http://laravel.com/docs/5.1/migrations#creating-columns) for the list of available types and modifiers.

```
php artisan wn:migration tasks --schema="amount:decimal.5,2:after.'size':default.8 title:string:nullable"
```
gives:

```php
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksMigration extends Migration
{
    
    public function up()
    {
        Schema::create('tasks', function(Blueprint $table) {
            $table->increments('id');
            $table->decimal('amount', 5, 2)->after('size')->default(8);
            $table->string('title')->nullable();
            // Constraints declaration
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('tasks');
    }
}
```

- **--keys**: the foreign keys of the table following the syntax `field:column:table:on_delete:on_update ...`. The `column` is optional ("id" by default). The `table` is optional if the field follows the naming convention `singular_table_name_id`. `on_delete` and `on_update` are optional too.

```
php artisan wn:migration tasks --keys="category_type_id user_id:identifier:members:cascade"
```
gives:

```php
//...
$table->foreign('category_type_id')
    ->references('id')
    ->on('category_types');

$table->foreign('user_id')
    ->references('identifier')
    ->on('members')
    ->onDelete('cascade');
```

### Pivot Table Generator

The `wn:pivot-table` command is used to generate a migration to create a pivot table between two models. It has the following syntax:

```
wn:pivot-table model1 model2 [--file=...]
```

- **model1** and **model2**: names of the two models (or the two tables if the models don't follow the naming conventions)

- **--file**: The migration file name. By default the name follows the patern `date_time_create_table_name.php`.

```
php artisan wn:pivot-table Tag Project
```
gives:

```php
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectTagMigration extends Migration
{
    
    public function up()
    {
        Schema::create('project_tag', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned()->index();
            $table->integer('tag_id')->unsigned()->index();
            $table->foreign('project_id')
                ->references('id')
                ->on('projects');
            $table->foreign('tag_id')
                ->references('id')
                ->on('tags');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('project_tag');
    }
}
```

### Controller Generator

There are two commands for controllers. The first one is `wn:controller:rest-actions` which generates a trait used by all generated controllers. This trait includes the following methods:

- `all()` : returns all the model entries as JSON.

- `get($id)` : returns a specific model by id as JSON.

- `add(Request $request)` : adds a new model or returns validation errors as JSON.

- `put(Request $request, $id)` : updates a model or returns validation errors as JSON.

- `remove($id)` : removes an entry by id.

Note that the trait doesn't use the common used methods on Laravel (like index, store, ...) to avoid conflicts. Which enables you to use this trait with controllers you already have in your application.

The second command is `wn:controller` which actually generates the controller. The syntax of this command is `wn:controller model [--no-routes]`.

- **model**: Name of the model (with namespace if not `App`).

- **--no-routes**: Since routes are generated by default for the controller, this option is used to tell the generator "do not generate routes !".

`php artisan wn:controller Task --no-routes` gives:


```php
<?php namespace App\Http\Controllers;


class TasksController extends Controller {

	const MODEL = "App\\Task";

	use RESTActions;

}
```

### Routes Generator

The `wn:route` command is used to generate RESTfull routes for a controller. It has the following syntax:

`wn:route resource [--controller=...]`

- **resource**: the resource name to use in the URLs.

- **--controller**: the corresponding controller. If missing it's deducted from the resource name.

`php artisan wn:route project-type` adds the following routes:

```php
$app->get('project-type', 'ProjectTypesController@all');
$app->get('project-type/{id}', 'ProjectTypesController@get');
$app->post('project-type', 'ProjectTypesController@add');
$app->put('project-type/{id}', 'ProjectTypesController@put');
$app->delete('project-type/{id}', 'ProjectTypesController@remove');
```

### Resource Generator

The `wn:resource` command makes it very easy to generate a RESTful resource. It generates a model, migration, controller and routes. The syntax is : `wn:resource name fields [--has-many=...] [--has-one=...] [--belongs-to=...] [--migration-file=...]`

- **name**: the name of the resource used in the URLs and to determine the model, table and controller names. 

- **fields**: specifies the fields of the resource along with schema and validation rules. It follows the syntax `name;schema;rules;tags ...`
	
	- name: the name of the field

	- schema: the schema following the syntax in the model generator. (note that the name is not part of the schema like on the model generator)

	- rules: the validation rules

	- tags: additional tags separated by commas. The possible tags are:

		- `fillable`: add this field to the fillable array of the model.

		- `date`: add this field to the dates array of the model.

		- `key`: this field is a foreign key.

- **--has-one**, **--has-many** and **--belongs-to** are the same as for the `wn:model` command.

- **--migration-file**: passed to the `wn:migration` as the `--file` option.

### Multiple Resources From File

The `wn:resources` (note the "s" in "resources") command takes the generation process to an other level by parsing a file and generating multiple resources based on it. The syntax is 

```
wn:resources filename
```

This generator is smart enough to add foreign keys automatically when finding a belongsTo relation. It also generates pivot tables for belongsToMany relations automatically.

The file given to the command should be a valid YAML file ( for the moment, support of other types like XML or JSON could be added in the future). An example is the following:

```yaml
---
Store:
  hasMany: products
  fields:
    name:
      schema: string:50 unique
      rules: required|min:3
      tags: fillable
Product:
  belongsTo: store
  fields:
    name:
      schema: string
      rules: required
      tags: fillable
    store_id:
      schema: integer unsigned
      rules: required numeric
      tags: fillable key
    desc:
      schema: text nullable
      tags: fillable
    published_at:
      schema: date
      rules: date
      tags: date fillable
    price:
      schema: 'decimal:5,2' # need quotes when using ','
      rules: numeric
      tags: fillable
```

## Testing

To test the generators, I included a fresh lumen installation under the folder `lumen-test` to which I added this package and have written some acceptance tests using [Codeception](http://codeception.com/). To run tests you just have to execute the `install.sh` to install dependencies then execute `test.sh`.

## Development Notes

- **Version 1.0.0**

	- Model Generator

	- Migration Generator

	- Controller Generator

	- Routes Generator

	- Resource Generator

	- Multiple Resources From File

- **Version 1.1.0**

	- Pivot table generator added.

	- belongsToMany relationship added to model generator.

	- Multiple resources generator adds foreign keys for belongsTo relationships automatically.

	- Multiple resources generator adds pivot tables for belongsToMany relationships automatically.

	- Generated migrations file names changed to be supported by `migrate` command.

## Contributing

Pull requests are welcome :D

