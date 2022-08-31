<?php

declare(strict_types=1);

namespace Robot\Controllers;

use Phalcon\Db\Enum;
use Robot\Library\Helper;
use Robot\Models\Params;

class FatherTask extends \Phalcon\Cli\Task
{
    public function mainAction(string $task)
    {
        $params = $this->getParams($task);

        $message = $this->transport->createMessage();
        $message->recipient($params->recipient);
        $message->header($params->header);
        $message->subject($params->description);

        if ($params->type === 'sql') {
            $sql = Helper::getSqlTask($task);
            $content = $this->compute($sql);
        } elseif ($params->type === 'command') {
            $command = Helper::getCommandTask($task);
            $content = $this->execCommand($command, $params->option);
        }
        if (empty($content)) {
            echo $this->locale->_('empty', ['task' => $task]) . PHP_EOL;
            return;
        }

        $message->content($content);

        // Send message
        if ($message->send()) {
            echo $this->locale->_('success', ['task' => $task]) . PHP_EOL;
        } else {
            new \Phalcon\Exception($this->locale->_('unsuccess', ['task' => $task]));
        }
    }

    private function compute(string $sql): array
    {
        return $this->db->fetchAll($sql, Enum::FETCH_NUM);
    }

    private function execCommand($command, $env_vars = null): array
    {
        $output = [];

        $descriptors = [['pipe', 'r'], ['pipe', 'w']];
        $option = $env_vars !== null ? ['OPTION' => $env_vars] : null;
        $handle = proc_open($command, $descriptors, $pipes, null, $option);
        $contents = explode(PHP_EOL, stream_get_contents($pipes[1]));

        foreach ($contents as $row) {
            if (empty($row)) {
                continue;
            }
            $output[] = explode("\t", $row);
        }

        fclose($pipes[0]);
        fclose($pipes[1]);
        proc_close($handle);

        return $output;
    }

    private function getParams(string $task): Params
    {
        list($type, $description, $header, $recipient, $option) = Helper::getIniTask($task);
        $recipient ??= $this->getRecipient();
        return new Params($type, $description, $header, $recipient, $option);
    }

    private function getRecipient(): string
    {
        $transport = $this->config->app->transport;
        return $this->config->$transport->recipient;
    }
}
