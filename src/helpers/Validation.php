<?php

class Validation {
    
    private $errors = [];
    
    public function required($field, $value, $message = null) {
        if (empty(trim($value))) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' is required';
        }
        return $this;
    }
    
    public function email($field, $value, $message = null) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? 'Invalid email format';
        }
        return $this;
    }
    
    public function minLength($field, $value, $min, $message = null) {
        if (!empty($value) && strlen($value) < $min) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must be at least {$min} characters";
        }
        return $this;
    }
    
    public function maxLength($field, $value, $max, $message = null) {
        if (!empty($value) && strlen($value) > $max) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must not exceed {$max} characters";
        }
        return $this;
    }
    
    public function numeric($field, $value, $message = null) {
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' must be a number';
        }
        return $this;
    }
    
    public function match($field, $value, $matchValue, $message = null) {
        if ($value !== $matchValue) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' does not match';
        }
        return $this;
    }
    
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function getError($field) {
        return isset($this->errors[$field]) ? $this->errors[$field] : null;
    }
}