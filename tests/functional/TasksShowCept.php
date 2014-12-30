<?php
$I = new TestGuy($scenario);
$I->wantTo('Show a task assigned to Jane');
$task = $I->haveTask(['assigned_name' => 'Jane Doe']);

$I->haveHttpHeader('Content-Type','application/json');
$I->sendGET("/task/{$task->id}?format=json");
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"assigned_name":"Jane Doe"');
