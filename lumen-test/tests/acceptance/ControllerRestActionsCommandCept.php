<?php 
$I = new AcceptanceTester($scenario);

$I->wantTo('generate the REST actions trait');
$I->runShellCommand('php artisan wn:controller:rest-actions');
$I->seeInShellOutput('REST actions trait generated');
$I->seeFileFound('./app/Http/Controllers/RESTActions.php');
$I->openFile('./app/Http/Controllers/RESTActions.php');
$I->seeInThisFile('trait RESTActions {');
