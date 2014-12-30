<?php

class Vreasy_ZendDbAdapters_Magentopdomysql extends \Magento\DB\Adapter\Pdo\Mysql {

    public function __construct(
        array $config = array()
    ) {
        $this->_dirs = new \Magento\App\Dir('logs');
        $this->string = new \Magento\Stdlib\String();
        $this->dateTime = new \Magento\Stdlib\DateTime();

        parent::__construct(
            $this->_dirs,
            $this->string,
            $this->dateTime,
            $config
        );
    }
}
