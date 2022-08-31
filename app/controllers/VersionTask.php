<?php

declare(strict_types=1);

namespace Robot\Controllers;

class VersionTask extends \Phalcon\Cli\Task
{
    /**
     * Version task
     *
     * @return void
     */
    public function mainAction()
    {
        echo '4.0' . PHP_EOL;
    }
}
