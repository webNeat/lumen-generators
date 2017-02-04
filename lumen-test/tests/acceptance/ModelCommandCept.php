<?php
$I = new AcceptanceTester($scenario);

$I->wantTo('generate a model without fillable fields or dates');
$I->runShellCommand('php artisan wn:model TestingModel --path=tests/tmp --force=true');
$I->seeInShellOutput('TestingModel model generated');
$I->seeFileFound('./tests/tmp/TestingModel.php');
$I->openFile('./tests/tmp/TestingModel.php');

$I->seeFileContentsEqual('<?php namespace Tests\Tmp;

use Illuminate\Database\Eloquent\Model;

class TestingModel extends Model {

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    // Relationships

}
');
$I->deleteFile('./tests/tmp/TestingModel.php');

$I->wantTo('generate a model without fillable fields, dates or timestamps');
$I->runShellCommand('php artisan wn:model TestingModel --path=tests/tmp --force=true --timestamps=false');
$I->seeInShellOutput('TestingModel model generated');
$I->seeFileFound('./tests/tmp/TestingModel.php');
$I->openFile('./tests/tmp/TestingModel.php');

$I->seeFileContentsEqual('<?php namespace Tests\Tmp;

use Illuminate\Database\Eloquent\Model;

class TestingModel extends Model {

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    public $timestamps = false;

    // Relationships

}
');
$I->deleteFile('./tests/tmp/TestingModel.php');

$I->wantTo('generate a model with fillable fields');
$I->runShellCommand('php artisan wn:model TestingModel --fillable=name,title --path=tests/tmp');
$I->seeFileFound('./tests/tmp/TestingModel.php');
$I->openFile('./tests/tmp/TestingModel.php');
$I->seeInThisFile('protected $fillable = ["name", "title"];');
$I->deleteFile('./tests/tmp/TestingModel.php');

$I->wantTo('generate a model with dates fields');
$I->runShellCommand('php artisan wn:model TestingModel --dates=started_at --path=tests/tmp');
$I->seeFileFound('./tests/tmp/TestingModel.php');
$I->openFile('./tests/tmp/TestingModel.php');
$I->seeInThisFile('protected $dates = ["started_at"];');
$I->deleteFile('./tests/tmp/TestingModel.php');

$I->wantTo('generate a model with relations');
$I->runShellCommand('php artisan wn:model TestingModel --has-many=accounts --belongs-to="owner:App\User" --has-one=number:Phone --path=tests/tmp');
$I->seeFileFound('./tests/tmp/TestingModel.php');
$I->openFile('./tests/tmp/TestingModel.php');
$I->seeInThisFile('
    public function accounts()
    {
        return $this->hasMany("Tests\\Tmp\\Account");
    }
');
$I->seeInThisFile('
    public function owner()
    {
        return $this->belongsTo("App\\User");
    }
');
$I->seeInThisFile('
    public function number()
    {
        return $this->hasOne("Tests\\Tmp\\Phone");
    }
');
$I->deleteFile('./tests/tmp/TestingModel.php');

$I->wantTo('generate a model with validation rules');
$I->runShellCommand('php artisan wn:model TestingModel --rules="name=required age=integer|min:13 email=email|unique:users,email_address" --path=tests/tmp');
$I->seeFileFound('./tests/tmp/TestingModel.php');
$I->openFile('./tests/tmp/TestingModel.php');
$I->seeInThisFile(
"    public static \$rules = [\n" .
"        \"name\" => \"required\"," . PHP_EOL .
"        \"age\" => \"integer|min:13\"," . PHP_EOL .
"        \"email\" => \"email|unique:users,email_address\",\n".
"    ];"
);

$I->deleteFile('./tests/tmp/TestingModel.php');

$I->wantTo('generate a model with softDeletes');
$I->runShellCommand('php artisan wn:model TestingModel --soft-deletes=true --path=tests/tmp --force=true');
$I->seeFileFound('./tests/tmp/TestingModel.php');
$I->openFile('./tests/tmp/TestingModel.php');
$I->seeFileContentsEqual('<?php namespace Tests\Tmp;

use Illuminate\Database\Eloquent\Model;

class TestingModel extends Model {

    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    // Relationships

}
');
$I->deleteFile('./tests/tmp/TestingModel.php');
