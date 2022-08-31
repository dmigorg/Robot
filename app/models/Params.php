<?php

namespace Robot\Models;

class Params
{
    public string $type;
    public string $description;
    public string $header;
    public string $recipient;
    public ?string $option;

    /**
     * Params construct
     *
     * @param string type
     * @param string description
     * @param string header
     * @param string recipient
     * @param string option
     *
     * @return void
     */
    public function __construct(string $type, string $description, string $header, string $recipient, ?string $option = null)
    {
        $this->type = $type;
        $this->description = $description;
        $this->header = $header;
        $this->recipient = $recipient;
        $this->option = $option;
    }
}
