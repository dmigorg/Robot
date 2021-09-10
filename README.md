Робот МСЭ

Предназначена для уведовления пользователей про определенным заданиям. Для отправки уведомлений доступные два транспорта SMTP и XMPP.

Доступные команды:
 robot version - Версия программы
 robot help - Вывод доступных команд
 robot cron - Запуск планировщика заданий

Запуск задания:
 robot epgu - Необработанные заявления с ЕПГУ
 robot fri - Данные не переданные во ФРИ
 robot remd - Необработанные электронные направления
 robot request - Незакрытые направления/заявления

Все настройки производится в файле config.ini
[app]
transport = xmpp ; xmpp или mail, для выбора типа транспорта

[xmpp]
host     = host ; имя jabber-сервер 
port     = 5222 ; порт jabber-сервер
username = username ; имя пользователя и пароль 
password = password ; для подключения к серверу
resource = robot ; название ресура
sender   = username@host ; имя получателя

[mail]
driver      = smtp ; имя протокола
host        = smtp.yandex.com ; имя сервера
port        = 465 ; порт сервера
encryption  = ssl ; использование шифрования
username    = username@yandex.com ; имя пользователя и пароль
password    = password ; для подключения к серверу
from[email] = username@yandex.com ; адрес отправителя
from[name]  = EAVIIAS Robot ; имя отправителя
to[email]   = to@yandex.com ; имя получателя
;bcc[email]   = hide@gmail.com ; имя получателя скрытой копии

[task]
;https://crontab.guru/
epgu = 0 8-15 * * 1-5
fri =  0 8-15/5 * * 1-5
remd = 0 8-15 * * 1-5
request = 0 8-15 * * 1-5

