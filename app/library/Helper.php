<?php
declare(strict_types=1);

namespace Robot\Library;

class Helper
{
  public static function tasksName() : array
  {
    return array_slice(scandir(TASK_PATH), 2);
  }

  public static function getIniTask(string $task) : array
  {
    $path = TASK_PATH."/$task"; 
    $ini = parse_ini_file("$path/task.ini");
    return [
      $ini['description'],
      $ini['header']
    ];
  }

  public static function getSqlTask(string $task) : string
  {
    $path = TASK_PATH."/$task";
    if(file_exists("$path/custom.sql"))
      return file_get_contents("$path/custom.sql");
    else 
      return file_get_contents("$path/task.sql");
  }
}