<?php

namespace App\Services;

class ValidationService {
    private $errors = [];

    public function validate($data, $rules) {
        $this->errors = [];

        foreach ($rules as $field => $ruleSet) {
            $rulesArray = explode('|', $ruleSet);
            $value = $data[$field] ?? null;

            foreach ($rulesArray as $rule) {
                $this->applyRule($field, $value, $rule, $data);
            }
        }

        return empty($this->errors);
    }

    private function applyRule($field, $value, $rule, $data) {
        if (strpos($rule, ':') !== false) {
            list($ruleName, $parameter) = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $parameter = null;
        }

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->errors[$field][] = ucfirst($field) . ' is required';
                }
                break;

            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = ucfirst($field) . ' must be a valid email';
                }
                break;

            case 'min':
                if ($value && strlen($value) < $parameter) {
                    $this->errors[$field][] = ucfirst($field) . " must be at least {$parameter} characters";
                }
                break;

            case 'max':
                if ($value && strlen($value) > $parameter) {
                    $this->errors[$field][] = ucfirst($field) . " must not exceed {$parameter} characters";
                }
                break;

            case 'numeric':
                if ($value && !is_numeric($value)) {
                    $this->errors[$field][] = ucfirst($field) . ' must be numeric';
                }
                break;

            case 'in':
                $allowed = explode(',', $parameter);
                if ($value && !in_array($value, $allowed)) {
                    $this->errors[$field][] = ucfirst($field) . ' must be one of: ' . implode(', ', $allowed);
                }
                break;

            case 'unique':
                list($table, $column) = explode(',', $parameter);
                if ($value && $this->checkUnique($table, $column, $value)) {
                    $this->errors[$field][] = ucfirst($field) . ' already exists';
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value && (!isset($data[$confirmField]) || $value !== $data[$confirmField])) {
                    $this->errors[$field][] = ucfirst($field) . ' confirmation does not match';
                }
                break;
        }
    }

    private function checkUnique($table, $column, $value) {
        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
        $stmt->execute([$value]);
        return $stmt->fetchColumn() > 0;
    }

    public function getErrors() {
        return $this->errors;
    }
}
