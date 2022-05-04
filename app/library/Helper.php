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
    
    if(file_exists("$path/custom.ini")){
      $customini = parse_ini_file("$path/custom.ini");
      $recipient = $customini['recipient'] ?? null;
      $option = $customini['option'] ?? null;
    }

    return [
      $ini['type'],
      $ini['description'],
      $ini['header'],
      $recipient ?? null,
      $option ?? $ini['option'] ?? null
    ];
  }

  public static function getSqlTask(string $task) : string
  {
    $path = TASK_PATH."/$task";
    if(file_exists("$path/custom.sql"))
      $sql = file_get_contents("$path/custom.sql");
    else 
      $sql = file_get_contents("$path/task.sql");
    
    return $sql;
  }

  public static function getCommandTask(string $task) : string
  {
    $path = TASK_PATH."/$task";
    if(file_exists("$path/custom.cmd"))
      $command = file_get_contents("$path/custom.cmd");
    else 
      $command = file_get_contents("$path/task.cmd");
    
    return $command;
  }
}