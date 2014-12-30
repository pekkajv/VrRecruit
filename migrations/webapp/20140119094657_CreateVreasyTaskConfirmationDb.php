<?php

class CreateVreasyTaskConfirmationDb extends Ruckusing_Migration_Base
{
    public function up()
    {
        if (getenv('APPLICATION_ENV') == 'development') {
            $this->create_database("vreasy_task_confirmation");
        } elseif (getenv('APPLICATION_ENV') == 'test') {
            $this->create_database("vreasy_task_confirmation_test");
        }
    }//up()

    public function down()
    {
        if (getenv('APPLICATION_ENV') == 'development') {
            $this->drop_database("vreasy_task_confirmation");
        } elseif (getenv('APPLICATION_ENV') == 'test') {
            $this->drop_database("vreasy_task_confirmation_test");
        }
    }//down()
}
