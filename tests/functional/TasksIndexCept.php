<?php
$I = new TestGuy($scenario);
$I->wantTo('List all the tasks');

$I->haveHttpHeader('Content-Type','application/json');
$I->sendGET('/task?format=json');
$I->seeResponseCodeIs(200);
