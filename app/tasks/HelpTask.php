<?php
declare(strict_types=1);

namespace Robot\Tasks;

use Robot\Library\Helper;

class HelpTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        $commands = <<<END
        Доступные команды:
        robot task <Название_Задания> - Запуск задания
        robot cron - Запуск планировщика заданий
        robot help - Вывод доступных команд
        robot version - Версия программы\n\n
        END;

        echo $commands;
        echo "Запуск задания:\n";
        echo $this->getNameTask();
    }

    private function getNameTask() : string
    {
        $tasks = [];
        foreach(Helper::tasksName() as $task) {
            $ini = parse_ini_file(TASK_PATH."/$task/task.ini");
            $tasks[]= 'robot task '.$task.' - '. $ini['description'];
        } 
        
        return implode(PHP_EOL, $tasks);
    }
}