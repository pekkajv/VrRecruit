<?php

namespace Codeception\Module;

use Guzzle\Plugin\Mock\MockPlugin;
use Codeception\TestCase;

class GuzzleHelper extends \Codeception\Module
{
    public $client;
    public $plugin;

    function _before(TestCase $test)
    {
        $this->client = \Zend_Registry::get('GuzzleClient');
        $this->plugin = new MockPlugin();
        $this->client->addSubscriber($this->plugin);
    }

    function _after(TestCase $test)
    {
        $this->plugin->flush();
        $this->plugin->clearQueue();
    }

    public function addResponse($response)
    {
        $this->plugin->addResponse($response);
    }

    public function addException($exception)
    {
        $this->plugin->addException($exception);
    }

    // TODO: Add more methods so to act as a wrapper here
    // See http://api.guzzlephp.org/class-Guzzle.Plugin.Mock.MockPlugin.html
}
