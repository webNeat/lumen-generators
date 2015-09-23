<?php 
$I = new AcceptanceTester($scenario);

$I->wantTo('generate a model without fillable fields or dates');
$I->runShellCommand('php artisan wn:model TestingModel --path=tests/tmp');
$I->seeInShellOutput('Model TestingModel Generated');
$I->seeFileFound('./tests/tmp/TestingModel.php');
$I->openFile('./tests/tmp/TestingModel.php');

$I->seeFileContentsEqual('<?php namespace Tests\Tmp;

use Illuminate\Database\Eloquent\Model;

class TestingModel extends Model {

	protected $fillable = [];

	protected $dates = [];

	public $rules = [
		// Validation rules
	];

	// Relationships

}
');

$I->wantTo('generate a model with fillable fields');
$I->runShellCommand('php artisan wn:model TestingModel --fillable=name,title --path=tests/tmp');
$I->seeInShellOutput('Model TestingModel Generated');
$I->seeFileFound('./tests/tmp/TestingModel.php');
$I->openFile('./tests/tmp/TestingModel.php');
$I->seeInThisFile('protected $fillable = ["name", "title"];');

$I->wantTo('generate a model with dates fields');
$I->runShellCommand('php artisan wn:model TestingModel --dates=started_at --path=tests/tmp');
$I->seeInShellOutput('Model TestingModel Generated');
$I->seeFileFound('./tests/tmp/TestingModel.php');
$I->openFile('./tests/tmp/TestingModel.php');
$I->seeInThisFile('protected $dates = ["started_at"];');

$I->wantTo('generate a model with relations');
$I->runShellCommand('php artisan wn:model TestingModel --has-many=accounts --belongs-to="owner:App\User" --has-one=number:Phone --path=tests/tmp');
$I->seeInShellOutput('Model TestingModel Generated');
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

$I->wantTo('generate a model with validation rules');
$I->runShellCommand('php artisan wn:model TestingModel --rules="name=required age=integer|min:13 email=email|unique:users,email_address" --path=tests/tmp');
$I->seeInShellOutput('Model TestingModel Generated');
$I->seeFileFound('./tests/tmp/TestingModel.php');
$I->openFile('./tests/tmp/TestingModel.php');
$I->seeInThisFile('
	public $rules = [
		"name" => "required",
		"age" => "integer|min:13",
		"email" => "email|unique:users,email_address",
	];
');
