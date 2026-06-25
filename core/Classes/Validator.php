<?php

namespace Core\Classes;

class Validator
{
    protected array $errors = [];

    public function validate(array $data, array $rules): array
    {
        foreach ($rules as $field => $ruleString) {
            $rulesArray = explode('|', $ruleString);
            $value = $data[$field] ?? null;

            foreach ($rulesArray as $rule) {
                $param = null;

                if (str_contains($rule, ':')) {
                    [$rule, $param] = explode(':', $rule);
                }

                $method = "validate" . ucfirst($rule);

                if (method_exists($this, $method)) {
                    $this->$method($field, $value, $param);
                }
            }
        }

        return $this->errors;
    }

    protected function validateRequired(string $field, mixed $value, mixed $param = null): void
    {
        if (is_null($value) || $value === '') {
            $this->addError($field, "The $field field is required.");
        }
    }

    protected function validateEmail(string $field, mixed $value, mixed $param = null): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "The $field field must be a valid email.");
        }
    }

    protected function validateMin(string $field, mixed $value, mixed $param = null): void
    {
        if (strlen($value) < (int)$param) {
            $this->addError($field, "The $field field must be at least $param characters.");
        }
    }

    protected function validateMax(string $field, mixed $value, mixed $param = null): void
    {
        if (strlen($value) > (int)$param) {
            $this->addError($field, "The $field field must be no more than $param characters.");
        }
    }

    protected function validateMatch(string $field, mixed $value, mixed $param = null): void
    {
        global $_POST;
        if (!isset($_POST[$param]) || $value !== $_POST[$param]) {
            $this->addError($field, "The $field field must match $param.");
        }
    }

    protected function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
