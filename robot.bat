@echo off
set CURRENT_PATH=%~dp0

php -d memory_limit=1G %CURRENT_PATH%robot %1 %2 %3
