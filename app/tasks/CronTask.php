<?php
declare(strict_types=1);

class CronTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        $results = $this->cron->runInForeground();
        
        foreach($results as $res){
            echo $res.PHP_EOL;
        }
    }
}
