<?php

$commands = <<<END
Доступные команды:
robot task - Список запланированных заданий 
robot task <Название_Задания> - Запуск задания
robot cron - Запуск планировщика заданий
robot help - Вывод доступных команд
robot version - Версия программы\n\n
END;

$messages = [
    'empty' => '%task% нет данных',
    'success' => '%task% данные успешно отправлены',
    'unsuccess' => '%task% данные не отправлены',
    'no-tasks' => 'Нет заданий на выполнение',
    'task-wrong' => 'Задание не существует',
    'help' => 'robot help - Вывод доступных команд',
    'help-task-name' => 'robot task <Название_Задания> - Запуск задания',
    'list-task-plannig' => 'Список запланированных заданий:',
    'commands' => $commands,
    'help-task-start' => "Запуск задания:\n",
];
