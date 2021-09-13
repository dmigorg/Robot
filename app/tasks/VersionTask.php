<?php
declare(strict_types=1);

namespace Robot\Task;

class VersionTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        echo '2.5';
    }
}
