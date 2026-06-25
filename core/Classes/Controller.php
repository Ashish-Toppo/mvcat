<?php

namespace Core\Classes;

class Controller
{
    protected Request $request;
    protected Response $response;

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    public function model(string $modelName)
    {
        $class = "App\\Models\\$modelName";

        if (class_exists($class)) {
            return new $class;
        }

        throw new \Exception("Model '$modelName' not found.");
    }

    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function redirect(string $url): void
    {
        $this->response->redirect($url);
    }

    protected function input(?string $key = null, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }

    protected function validate(array $rules): array
    {
        $validator = new Validator();
        return $validator->validate($this->request->all(), $rules);
    }
}
