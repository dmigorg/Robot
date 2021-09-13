<?php
declare(strict_types=1);

namespace Robot\Task;

class HelpTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        $help = <<<END
        Доступные команды:
        robot version - Версия программы
        robot help - Вывод доступных команд
        robot cron - Запуск планировщика заданий
        Запуск задания:
        robot epgu - Необработанные заявления с ЕПГУ
        robot fri - Данные не переданные во ФРИ
        robot remd - Необработанные электронные направления
        robot request - Незакрытые направления/заявления
        END;
        
        echo $help;
    }
}