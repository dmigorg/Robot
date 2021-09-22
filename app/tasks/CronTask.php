<?php
declare(strict_types=1);

namespace Robot\Tasks;

class CronTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        $results = $this->cron->runInForeground();

        if(!empty($results)){
            echo implode(PHP_EOL, $results);
        } else {
            echo 'No tasks available';
        }
    }
}
