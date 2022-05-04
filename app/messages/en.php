<?php
$commands = <<<END
Commands available:
robot task - List of scheduled tasks
robot task <Task_Name> - Starting task
robot cron - Starting the Task Scheduler
robot help - Output of available commands
robot version - Software version\n\n
END;

$messages = [
    'empty' => '%task% task empty data',
    'success' => '%task% task success send',
    'no-tasks' => 'No tasks available',
    'task-wrong' => 'Task name is wrong',
    'help' => 'robot help - Output of available commands',
    'help-task-name' => 'robot task <Task_Name> - Starting task',
    'list-task-plannig' => 'List of scheduled tasks:',
    'commands' => $commands,
    'help-task-start' => "Starting task:\n",
];