<?php

declare(strict_types=1);

namespace Robot\Controllers;

use Robot\Library\Helper;

class HelpTask extends \Phalcon\Cli\Task
{
    /**
     * Main action
     *
     * @return void
     */
    public function mainAction()
    {
        echo $this->locale->_('commands');
        echo $this->locale->_('help-task-start');
        echo $this->getNameTask();
    }

    /**
     * Get name task
     *
     * @return string
     */
    private function getNameTask(): string
    {
        $tasks = [];
        foreach (Helper::tasksName() as $task) {
            $ini = parse_ini_file(TASK_PATH . "/$task/task.ini");
            $tasks[] = 'robot task ' . $task . ' - ' . $ini['description'];
        }

        return implode(PHP_EOL, $tasks) . PHP_EOL;
    }
}
