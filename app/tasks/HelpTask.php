<?php
declare(strict_types=1);

class HelpTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        echo <<<END
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
    }
}
