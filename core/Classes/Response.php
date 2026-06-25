<?php

namespace Core\Classes;

class Response
{
    public function status(int $code): self
    {
        http_response_code($code);
        return $this;
    }

    public function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    public function send(string $content): void
    {
        echo $content;
    }
}
