<?php

$cliIndex = implode(DIRECTORY_SEPARATOR, ['vreasy', 'application', 'cli', 'cliindex.php']);
require_once($cliIndex);

use Vreasy\Models\Task;

class InsertSomeTasks extends Ruckusing_Migration_Base
{
    public function up()
    {
        foreach ([1,2,3] as $i) {
            $t = Task::instanceWith([
                'deadline' => (new \DateTime("+$i days"))->format(DATE_FORMAT),
                'assigned_name' => 'John Doe',
                'assigned_phone' => '+55 555-555-555',
            ]);
            $t->save();
        }
    }//up()

    public function down()
    {
    }//down()
}
