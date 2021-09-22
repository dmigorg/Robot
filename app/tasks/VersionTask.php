<?php
declare(strict_types=1);

namespace Robot\Tasks;

class VersionTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        echo '3.0';
    }
}
