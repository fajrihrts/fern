<?php
/**
 * Input Validation Class
 * Centralized validation logic
 */

class Validator {
    private $data = [];
    private $rules = [];
    private $errors = [];
    private $messages = [];
    
    public function __construct($data, $rules, $messages = []) {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
    }
    
    /**
     * Validate data
     */
    public function validate() {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;
            
            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Apply validation rule
     */
    private function applyRule($field, $value, $rule) {
        // Parse rule with parameters (e.g., "max:255")
        $parts = explode(':', $rule);
        $ruleName = $parts[0];
        $params = isset($parts[1]) ? explode(',', $parts[1]) : [];
        
        $method = 'validate' . ucfirst($ruleName);
        
        if (method_exists($this, $method)) {
            $result = $this->$method($value, $params);
            
            if (!$result) {
                $this->addError($field, $ruleName, $params);
            }
        }
    }
    
    /**
     * Add error message
     */
    private function addError($field, $rule, $params = []) {
        $key = "$field.$rule";
        
        if (isset($this->messages[$key])) {
            $message = $this->messages[$key];
        } else {
            $message = $this->getDefaultMessage($field, $rule, $params);
        }
        
        $this->errors[$field] = $message;
    }
    
    /**
     * Get default error message
     */
    private function getDefaultMessage($field, $rule, $params) {
        $fieldName = ucfirst(str_replace('_', ' ', $field));
        
        $messages = [
            'required' => "$fieldName wajib diisi",
            'email' => "$fieldName harus berupa email yang valid",
            'min' => "$fieldName minimal {$params[0]} karakter",
            'max' => "$fieldName maksimal {$params[0]} karakter",
            'numeric' => "$fieldName harus berupa angka",
            'alpha' => "$fieldName hanya boleh berisi huruf",
            'alphanumeric' => "$fieldName hanya boleh berisi huruf dan angka",
            'url' => "$fieldName harus berupa URL yang valid",
            'date' => "$fieldName harus berupa tanggal yang valid",
            'confirmed' => "$fieldName tidak cocok dengan konfirmasi",
            'unique' => "$fieldName sudah digunakan",
            'exists' => "$fieldName tidak ditemukan",
            'in' => "$fieldName tidak valid",
            'file' => "$fieldName harus berupa file",
            'image' => "$fieldName harus berupa gambar",
            'mimes' => "$fieldName harus berupa file dengan tipe: " . implode(', ', $params),
            'size' => "$fieldName maksimal {$params[0]} KB",
        ];
        
        return $messages[$rule] ?? "$fieldName tidak valid";
    }
    
    /**
     * Get errors
     */
    public function errors() {
        return $this->errors;
    }
    
    /**
     * Check if validation failed
     */
    public function fails() {
        return !empty($this->errors);
    }
    
    // Validation Rules
    
    protected function validateRequired($value) {
        return !empty($value) || $value === '0' || $value === 0;
    }
    
    protected function validateEmail($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    protected function validateMin($value, $params) {
        $min = $params[0];
        return strlen($value) >= $min;
    }
    
    protected function validateMax($value, $params) {
        $max = $params[0];
        return strlen($value) <= $max;
    }
    
    protected function validateNumeric($value) {
        return is_numeric($value);
    }
    
    protected function validateAlpha($value) {
        return preg_match('/^[a-zA-Z]+$/', $value);
    }
    
    protected function validateAlphanumeric($value) {
        return preg_match('/^[a-zA-Z0-9]+$/', $value);
    }
    
    protected function validateUrl($value) {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
    
    protected function validateDate($value) {
        return strtotime($value) !== false;
    }
    
    protected function validateConfirmed($value, $params) {
        $field = $params[0] ?? null;
        if (!$field) return false;
        
        return $value === ($this->data[$field] ?? null);
    }
    
    protected function validateIn($value, $params) {
        return in_array($value, $params);
    }
    
    protected function validateFile($value) {
        return isset($value['tmp_name']) && is_uploaded_file($value['tmp_name']);
    }
    
    protected function validateImage($value) {
        if (!$this->validateFile($value)) return false;
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $value['tmp_name']);
        finfo_close($finfo);
        
        return in_array($mimeType, $allowedTypes);
    }
    
    protected function validateMimes($value, $params) {
        if (!$this->validateFile($value)) return false;
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $value['tmp_name']);
        finfo_close($finfo);
        
        return in_array($mimeType, $params);
    }
    
    protected function validateSize($value, $params) {
        if (!$this->validateFile($value)) return false;
        
        $maxSize = $params[0] * 1024; // Convert KB to bytes
        return $value['size'] <= $maxSize;
    }
    
    /**
     * Static helper for quick validation
     */
    public static function make($data, $rules, $messages = []) {
        return new self($data, $rules, $messages);
    }
}
