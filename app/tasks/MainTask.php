<?php
declare(strict_types=1);

namespace Robot\Tasks;

use Robot\Library\Helper;

class MainTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        switch ($this->task['name']) {
            case 'task':
                $args = $this->task['arg'];
                
                if(empty($args)) {
                    echo 'Task name is empty'.PHP_EOL;
                    echo 'robot task <Название_Задания>';
                    break;
                } 
                
                if(!in_array($args, Helper::tasksName())) {
                    echo 'Task name is wrong'.PHP_EOL;
                    echo 'robot help - Вывод доступных команд';
                    break;
                }

                $this->console->handle(['task' => 'god', 'params' => [$args]]);
                break;

            case 'cron':
                $this->console->handle(['task' => 'cron']);
                break;
            
            case 'version':
                $this->console->handle(['task' => 'version']);
                break;

            default:
                $this->console->handle(['task' => 'help']);
        }
    }
}
