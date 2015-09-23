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
	
}
');

$I->wantTo('generate a model with fillable fields');
$I->runShellCommand('php artisan wn:model TestingModel --fillable=name,title --path=tests/tmp');
$I->seeInShellOutput('Model TestingModel Generated');
$I->seeFileFound('./tests/tmp/TestingModel.php');
$I->openFile('./tests/tmp/TestingModel.php');
$I->seeFileContentsEqual('<?php namespace Tests\Tmp;

use Illuminate\Database\Eloquent\Model;

class TestingModel extends Model {

	protected $fillable = ["name", "title"];

	protected $dates = [];
	
}
');

$I->wantTo('generate a model with dates fields');
$I->runShellCommand('php artisan wn:model TestingModel --dates=started_at --path=tests/tmp');
$I->seeInShellOutput('Model TestingModel Generated');
$I->seeFileFound('./tests/tmp/TestingModel.php');
$I->openFile('./tests/tmp/TestingModel.php');
$I->seeFileContentsEqual('<?php namespace Tests\Tmp;

use Illuminate\Database\Eloquent\Model;

class TestingModel extends Model {

	protected $fillable = [];

	protected $dates = ["started_at"];
	
}
');

$I->wantTo('generate a model with relations');
$I->runShellCommand('php artisan wn:model TestingModel --has-many=accounts,friends:App\User,numbers:Phone --path=tests/tmp');
$I->seeInShellOutput('Model TestingModel Generated');
$I->seeFileFound('./tests/tmp/TestingModel.php');
$I->openFile('./tests/tmp/TestingModel.php');
$I->seeFileContentsEqual('<?php namespace Tests\Tmp;

use Illuminate\Database\Eloquent\Model;

class TestingModel extends Model {

	protected $fillable = [];

	protected $dates = [];

	public function accounts(){
		return $this->hasMany("Tests\\Tmp\\Account");
	}

	public function friends(){
		return $this->hasMany("App\\User");
	}

	public function numbers(){
		return $this->hasMany("Tests\\Tmp\\Phone");
	}

}
');