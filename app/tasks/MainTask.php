<?php
declare(strict_types=1);

namespace Robot\Task;

class MainTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        echo "I'm robot";
    }
}
