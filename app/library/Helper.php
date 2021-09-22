<?php
declare(strict_types=1);

namespace Robot\Library;

class Helper
{
  public static function tasksName() : array
  {
    return array_slice(scandir(TASK_PATH), 2);
  }
}