<?php

class Vreasy_View_Filter_CssVersioning extends Zend_Filter_PregReplace {
    public function __construct($options = null) {
        parent::__construct($options);
        $appVersion = APP_VERSION;
        $this->setMatchPattern('/(<link.*)href=[\'\"](?!.*\?v=)([^\'\"]*)[\'\"]/');
        $this->setReplacement("$1href=\"$2?v=$appVersion\"$3");
    }
}
