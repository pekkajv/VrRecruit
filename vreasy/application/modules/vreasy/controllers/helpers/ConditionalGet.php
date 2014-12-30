<?php

class Vreasy_Controller_Action_Helper_ConditionalGet
    extends Zend_Controller_Action_Helper_Abstract
{

    public function direct() {
        return $this;
    }

    public function sendFreshWhen($options) {
        if ($this->isFresh($options)) {
            $this->sendNotModified($options);
        }
        else {
            $this->sendStaleInfo($options);
        }
    }

    /**
     * Sets the etag and/or last_modified on the response and checks it against
     * the client request. If the request doesn’t match the options provided,
     * the request is considered stale and should be generated from scratch.
     * Otherwise, it’s fresh and we don’t need to generate anything and a
     * reply of 304 Not Modified is sent.
     *
     * @return boolean true of the request was considered stale
     */
    public function isStale($options) {
        $isStale = false;
        if ($this->isFresh($options)) {
            $isStale = false;
            $this->sendNotModified($options);
        }
        else {
            $isStale = true;
            $this->sendStaleInfo($options);
        }
        return $isStale;
    }

    public function isFresh($options) {
        $etag = null; $last_modified = null;
        extract($options, EXTR_IF_EXISTS);
        if ($etag) {
            $etag = $this->etagFor($etag);
        }

        return  ($etag || $last_modified) &&
                ($this->etagMatches($etag) || $this->isNotModified($last_modified)) &&
                (!in_array(APPLICATION_ENV, ['development']));
    }

    protected function sendStaleInfo($options) {
        $etag = null; $last_modified = null; $public = false;
        extract($options, EXTR_IF_EXISTS);

        if ($etag) {
            $this->getResponse()->setHeader('ETag', $this->etagKeyFrom($this->etagFor($etag)), true);
            $this->getResponse()->setHeader('Vary', 'Cookie', true);
        }
        if ($last_modified) {
            $this->getResponse()->setHeader('Last-Modified',
                $this->lastModifiedFrom($last_modified), true);
        }
        if ($etag || $last_modified) {
            $this->getResponse()->setHeader(
                'Cache-Control',
                $public ? 'public' : 'private' , true
            );
        }
    }

    public function sendNotModified($options = [])
    {
        $public = false;
        extract($options, EXTR_IF_EXISTS);

        $this->getResponse()->setHeader(
            'Cache-Control',
            $public ? 'public' : 'private' , true
        );
        $this->getResponse()->setHttpResponseCode(304);
        $controller = $this->getActionController();
        $controller->getHelper('layout')->disableLayout();
        $controller->getHelper('viewRenderer')->setNoRender(true);
        $controller->getHelper('contextSwitchExt')->setNoRender(true);
        $this->getResponse()->sendResponse();
        $redirector = \Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        if ($redirector->getExit()) {
            exit;
        }
    }

    public function etagFor($value) {
        if ((is_array($value) || $value instanceof Traversable)) {
            $objects = [];
            foreach ($value as $item) {
                $objects[] = $this->etagFor($item);
            }
            sort($objects);
            return $objects;
        }
        if (isset($value->updated_at)) {
            return $value->updated_at;
        }
        elseif (isset($value->updated)) {
            return $value->updated;
        }
        else {
            return $value;
        }
    }

    protected function etagMatches($etag) {
        $ifNoneMatch = $this->getRequest()->getHeader('If-None-Match');
        return $ifNoneMatch && $ifNoneMatch == $this->etagKeyFrom($etag);
    }

    protected function isNotModified($lastModified) {
        $ifModifiedSince = $this->getRequest()->getHeader('If-Modified-Since');
        return $lastModified
            && $ifModifiedSince
            && strtotime($ifModifiedSince) >= strtotime($lastModified);
    }

    protected function etagKeyFrom($etag) {
        $etag = json_encode($etag);
        if (defined('APP_VERSION')) {
            $etag = APP_VERSION . "$etag";
        }
        return md5($etag);
    }

    protected function lastModifiedFrom($lastModified) {
        return gmdate('D, d M Y H:i:s \G\M\T', strtotime($lastModified));
    }

}
