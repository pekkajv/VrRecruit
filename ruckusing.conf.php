<?php

//----------------------------
// DATABASE CONFIGURATION
//----------------------------

set_include_path(implode(
    PATH_SEPARATOR,
    array(realpath(__DIR__ . '/library'), get_include_path())
));

require_once 'Zend/Config/Ini.php';
$config = new Zend_Config_Ini(realpath(
    __DIR__ . '/vreasy/application/configs/db.ini'
));

$enviroments = ['production', 'test', 'development'];

foreach ($enviroments as $env) {
    $enviroments[$env] = [
        'directory' => 'webapp',
        'type'      => 'mysql',
        'host'      => $config->$env->database->params->host,
        'port'      => 3306,
        'database'  => $config->$env->database->params->dbname,
        'user'      => $config->$env->database->params->username,
        'password'  => $config->$env->database->params->password,
    ];
}

$db_config = [
    'db' => $enviroments,
    'migrations_dir' => RUCKUSING_WORKING_BASE . DIRECTORY_SEPARATOR . 'migrations',
    'db_dir' => RUCKUSING_WORKING_BASE . DIRECTORY_SEPARATOR . 'db',
    'log_dir' => RUCKUSING_WORKING_BASE . DIRECTORY_SEPARATOR . 'logs',
    'ruckusing_base' => implode(
        DIRECTORY_SEPARATOR,
        ['vendor', 'ruckusing','ruckusing-migrations']
    )
];

function getRuckusingEnv($argv)
{
    $num_args = count($argv);

    $options = array();
    for ($i = 0; $i < $num_args; $i++) {
        $arg = $argv[$i];
        if (stripos($arg, '=') !== false) {
            list($key, $value) = explode('=', $arg);
            $key = strtolower($key);
            $options[$key] = $value;
            if ($key == 'env') {
                return $value;
            }
        }
    }
}

if ($env = getRuckusingEnv($argv)) {
    putenv('APPLICATION_ENV='.$env);
}

return $db_config;
