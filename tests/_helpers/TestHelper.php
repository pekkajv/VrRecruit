<?php

namespace Codeception\Module;

use Codeception\TestCase;
use Vreasy\Models\Task;

// here you can define custom functions for TestGuy
class TestHelper extends \Codeception\Util\Framework
{
    function _before(TestCase $test)
    {
        $this->client = $this->getModule('ZF1')->client;
        $crawler = new \ReflectionProperty(get_class($this->client), 'crawler');
        $crawler->setAccessible(true);
        $this->crawler = $crawler->getValue($this->client);
        $this->getModule('ZF1')->db->beginTransaction();
    }

    function _after(TestCase $test)
    {
        $this->getModule('ZF1')->db->rollback();
    }

    public function haveTask($params = [])
    {
        $I = $this->getModule('DbzHelper');
        $params = array_merge(
            [
                'deadline' => gmdate(DATE_FORMAT),
                'assigned_phone' => '+34666666666',
                'assigned_name' => 'John Doe',
            ],
            $params
        );

        if (isset($params['id'])) {
            $task = Task::findOrInit($params['id']);
            $task = Task::hydrate($task, $params);
        } else {
            $task = Task::instanceWith($params);
        }
        \PHPUnit_Framework_Assert::assertTrue((bool) $task->save());
        return $task;

    }

    public function seeHttpHeader($name, $value = null)
    {
        $client = $this->getModule('REST')->client;
        if ($value) {
            \PHPUnit_Framework_Assert::assertEquals(
                $client->getResponse()->getHeader($name),
                $value
            );
        }
        else {
            \PHPUnit_Framework_Assert::assertNotNull($client->getResponse()->getHeader($name));
        }
    }

    public function dontSeeHttpHeader($name, $value = null)
    {
        $client = $this->getModule('REST')->client;
        if ($value) {
            \PHPUnit_Framework_Assert::assertNotEquals(
                $client->getResponse()->getHeader($name),
                $value
            );
        }
        else {
            \PHPUnit_Framework_Assert::assertNull($client->getResponse()->getHeader($name));
        }
    }

    public function grabHttpHeader($name, $first = true)
    {
        $client = $this->getModule('REST')->client;
        return $client->getResponse()->getHeader($name, $first);
    }
}
