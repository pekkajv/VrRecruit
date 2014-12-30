<?php

class Vreasy_View_Filter_ScriptVersioning extends Zend_Filter_PregReplace {
    public function __construct($options = null) {
        parent::__construct($options);
        $appVersion = APP_VERSION;
        $this->setMatchPattern('/(<script.*)src=[\'\"](?!.*\?v=)([^\'\"]*)[\'\"]/');
        $this->setReplacement("$1src=\"$2?v=$appVersion\"$3");
    }
}
