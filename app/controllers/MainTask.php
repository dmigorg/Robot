<?php
declare(strict_types=1);

namespace Robot\Controllers;

use Robot\Library\Helper;

class MainTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        switch ($this->task['name']) {
            case 'task':
                $args = $this->task['arg'];
                
                if(empty($args)) {
                    echo 'Список запланированных заданий:'.str_repeat(PHP_EOL, 2);
                    foreach($this->config->task as $task=>$time) {
                        list($description) = Helper::getIniTask($task);
                        echo "robot task $task - $description".PHP_EOL;
                        echo " $time".str_repeat(PHP_EOL, 2);
                    }
                    echo 'robot task <Название_Задания> - Запуск задания';
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
