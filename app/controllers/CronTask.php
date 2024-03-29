<?php

declare(strict_types=1);

namespace Robot\Controllers;

class CronTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        $results = $this->cron->runInForeground();

        if (!empty($results)) {
            echo implode(PHP_EOL, $results) . PHP_EOL;
        } else {
            echo $this->locale->_('no-tasks') . PHP_EOL;
        }
    }
}
