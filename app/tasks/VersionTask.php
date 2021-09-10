<?php
declare(strict_types=1);

class VersionTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        echo $this->config->app->version;
    }
}
