<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Requests;

use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;

abstract class FormRequest
{
    protected $data;

    abstract public function rules(): array;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->validate();
    }

    public function validate(): void
    {
        $rules = $this->rules();
        $errors = [];
    
        foreach ($rules as $field => $validations) {
            // Check if the field is a nested field like billing_data.first_name
            if (strpos($field, '.*.') !== false) {
                $fieldParts = explode('.*.', $field);
                $mainField = $fieldParts[0];
    
                // Verify the main field is an array
                if (!isset($this->data[$mainField]) || !is_array($this->data[$mainField])) {
                    if (!isset($errors[$mainField]) || !in_array("The {$mainField} must be an array.", $errors[$mainField])) {
                        $errors[$mainField][] = "The {$mainField} must be an array.";
                    }
                    continue;
                }
    
                // Get nested value
                $fieldValue = $this->getNestedValue($this->data, $fieldParts);
            } else {
                $fieldValue = $this->data[$field] ?? null;
            }
    
            // Check if the field is required and missing
            if (!isset($fieldValue) && in_array('required', $validations)) {
                if (!isset($errors[$field]) || !in_array("The {$field} field is required.", $errors[$field])) {
                    $errors[$field][] = "The {$field} field is required.";
                }
                continue;
            }
    
            // Skip validation if the field is optional and not set
            if (in_array('optional', $validations) && !isset($fieldValue)) {
                continue;
            }
    
            // Validate field rules
            foreach ($validations as $validation) {
                if ($validation === 'required' || $validation === 'optional') {
                    continue;
                }
    
                if (!$this->validateField($field, $fieldValue, $validation)) {
                    $errorMessage = $this->getErrorMessage($field, $validation);
                    if (!isset($errors[$field]) || !in_array($errorMessage, $errors[$field])) {
                        $errors[$field][] = $errorMessage;
                    }
                }
            }
        }
    
        // Throw exception if there are validation errors
        if (!empty($errors)) {
            throw PaymentGatewayException::validationError('Validation failed', $errors);
        }
    }
    

    protected function getNestedValue(array $data, array $keys)
    {
        $key = array_shift($keys);
        if (isset($data[$key])) {
            if (count($keys) > 0) {
                return $this->getNestedValue($data[$key], $keys);
            }
            return $data[$key];
        }
        return null;
    }

    protected function validateField(string $field, $value, string $rule): bool
    {
        // Skip validation if the rule is "nullable" and the field is null
        if (($rule === 'nullable' || $rule === 'optional') && is_null($value)) {
            return true;
        }

        // if (($rule === 'optional') && !isset($rule)) {
        //     return true;
        // }

        switch ($rule) {
            case 'numeric':
                return is_numeric($value);
            case 'string':
                return is_string($value);
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'array':
                return is_array($value);
            case 'positive':
                return is_numeric($value) && $value > 0;
            case 'phone':
                return preg_match('/^\+?[0-9]{10,15}$/', $value);
            default:
                if (strpos($rule, 'min:') === 0) {
                    $min = (float) substr($rule, 4); // Use float for numeric comparisons
                    return is_numeric($value) && $value >= $min;
                }
                if (strpos($rule, 'max:') === 0) {
                    $max = (float) substr($rule, 4); // Use float for numeric comparisons
                    return is_numeric($value) && $value <= $max;
                }
                if (strpos($rule, 'in:') === 0) {
                    $allowedValues = explode(',', substr($rule, 3));
                    return in_array($value, $allowedValues);
                }
                return true;
        }
    }

    protected function getErrorMessage(string $field, string $rule): string
    {
        $messages = [
            'numeric' => "The {$field} must be a number.",
            'string' => "The {$field} must be a string.",
            'email' => "The {$field} must be a valid email address.",
            'array' => "The {$field} must be an array.",
            'positive' => "The {$field} must be positive.",
            'phone' => "The {$field} must be a valid phone number.",
        ];
    
        if (strpos($rule, 'min:') === 0) {
            $min = substr($rule, 4);
            return "The {$field} must be at least {$min}.";
        }
    
        if (strpos($rule, 'max:') === 0) {
            $max = substr($rule, 4);
            return "The {$field} must not exceed {$max}.";
        }
    
        if (strpos($rule, 'in:') === 0) {
            $allowedValues = substr($rule, 3);
            return "The {$field} must be one of the following: {$allowedValues}.";
        }
    
        return $messages[$rule] ?? "The {$field} failed {$rule} validation.";
    }
    
    public function validated(): array
    {
        return $this->filterData();
    }

    public function all(): array
    {
        return $this->data;
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->data, array_flip($keys));
    }

    public function organizeData(): array
    {
        $organizedData = [];
        $rules = $this->rules();

        foreach ($rules as $key => $value) {
            if (str_contains($key, '.*.')) {
                // فصل المفتاح على أساس العلامة `.*.`
                [$mainKey, $subKey] = explode('.*.', $key, 2);

                // إضافة المفتاح الرئيسي كمصفوفة إذا لم يكن موجودًا
                if (!isset($organizedData[$mainKey])) {
                    $organizedData[$mainKey] = [];
                }

                // إضافة المفتاح الفرعي إلى المصفوفة
                if (!in_array($subKey, $organizedData[$mainKey])) {
                    $organizedData[$mainKey][] = $subKey;
                }
            } else {
                // المفتاح الرئيسي كقيمة فقط
                if (!in_array($key, $organizedData)) {
                    $organizedData[] = $key;
                }
            }
        }

        return $organizedData;
    }

    function filterData()
    {
        $filteredData = [];

        $data = $this->data;
        $template = $this->organizeData();

        foreach ($template as $key => $value) {
            if (is_array($value)) {
                if (isset($data[$key]) && is_array($data[$key])) {
                    $filteredData[$key] = array_intersect_key($data[$key], array_flip($value));
                }
            } else {
                if (isset($data[$key])) {
                    $filteredData[$key] = $data[$key];
                } else {
                    $filteredData[$value] = $data[$value];
                }
            }
        }

        return $filteredData;
    }

}
