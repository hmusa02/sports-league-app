<?php

class ValidationHelper {
    
    // Validate email format
    public static function validateEmail($email) {
        if (empty($email)) {
            return ['valid' => false, 'message' => 'Email is required'];
        }
        
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Invalid email format'];
        }
        
        if (strlen($email) > 255) {
            return ['valid' => false, 'message' => 'Email is too long'];
        }
        
        return ['valid' => true, 'email' => $email];
    }
    
    // Validate password
    public static function validatePassword($password) {
        if (empty($password)) {
            return ['valid' => false, 'message' => 'Password is required'];
        }
        
        if (strlen($password) < 6) {
            return ['valid' => false, 'message' => 'Password must be at least 6 characters long'];
        }
        
        if (strlen($password) > 255) {
            return ['valid' => false, 'message' => 'Password is too long'];
        }
        
        return ['valid' => true];
    }
    
    // Validate required string field
    public static function validateRequiredString($value, $fieldName, $minLength = 1, $maxLength = 255) {
        if (empty($value) || !is_string($value)) {
            return ['valid' => false, 'message' => $fieldName . ' is required'];
        }
        
        $value = trim($value);
        
        if (strlen($value) < $minLength) {
            return ['valid' => false, 'message' => $fieldName . " must be at least {$minLength} characters long"];
        }
        
        if (strlen($value) > $maxLength) {
            return ['valid' => false, 'message' => $fieldName . " must be no more than {$maxLength} characters long"];
        }
        
        return ['valid' => true, 'value' => $value];
    }
    
    // Validate integer
    public static function validateInteger($value, $fieldName, $min = null, $max = null) {
        if (empty($value) && $value !== 0) {
            return ['valid' => false, 'message' => $fieldName . ' is required'];
        }
        
        if (!is_numeric($value)) {
            return ['valid' => false, 'message' => $fieldName . ' must be a valid number'];
        }
        
        $value = intval($value);
        
        if ($min !== null && $value < $min) {
            return ['valid' => false, 'message' => $fieldName . " must be at least {$min}"];
        }
        
        if ($max !== null && $value > $max) {
            return ['valid' => false, 'message' => $fieldName . " must be no more than {$max}"];
        }
        
        return ['valid' => true, 'value' => $value];
    }
    
    // Validate enum values
    public static function validateEnum($value, $fieldName, $allowedValues) {
        if (empty($value)) {
            return ['valid' => false, 'message' => $fieldName . ' is required'];
        }
        
        if (!in_array($value, $allowedValues)) {
            return ['valid' => false, 'message' => $fieldName . ' must be one of: ' . implode(', ', $allowedValues)];
        }
        
        return ['valid' => true, 'value' => $value];
    }
    
    // Sanitize string input to prevent XSS
    public static function sanitizeString($input) {
        if (!is_string($input)) {
            return $input;
        }
        
        // Remove null bytes
        $input = str_replace("\0", '', $input);
        
        // HTML encode to prevent XSS
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return trim($input);
    }
    
    // Validate and sanitize user registration data
    public static function validateUserRegistration($data) {
        $errors = [];
        $sanitized = [];
        
        // Validate first name
        $firstNameValidation = self::validateRequiredString($data['first_name'] ?? '', 'First name', 2, 50);
        if (!$firstNameValidation['valid']) {
            $errors[] = $firstNameValidation['message'];
        } else {
            $sanitized['first_name'] = self::sanitizeString($firstNameValidation['value']);
        }
        
        // Validate last name
        $lastNameValidation = self::validateRequiredString($data['last_name'] ?? '', 'Last name', 2, 50);
        if (!$lastNameValidation['valid']) {
            $errors[] = $lastNameValidation['message'];
        } else {
            $sanitized['last_name'] = self::sanitizeString($lastNameValidation['value']);
        }
        
        // Validate email
        $emailValidation = self::validateEmail($data['email'] ?? '');
        if (!$emailValidation['valid']) {
            $errors[] = $emailValidation['message'];
        } else {
            $sanitized['email'] = $emailValidation['email'];
        }
        
        // Validate password
        $passwordValidation = self::validatePassword($data['password'] ?? '');
        if (!$passwordValidation['valid']) {
            $errors[] = $passwordValidation['message'];
        } else {
            $sanitized['password'] = $data['password']; // Don't sanitize password
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $sanitized
        ];
    }
    
    // Validate and sanitize statistic data
    public static function validateStatisticData($data) {
        $errors = [];
        $sanitized = [];
        
        // Validate match_id
        $matchIdValidation = self::validateInteger($data['match_id'] ?? '', 'Match ID', 1);
        if (!$matchIdValidation['valid']) {
            $errors[] = $matchIdValidation['message'];
        } else {
            $sanitized['match_id'] = $matchIdValidation['value'];
        }
        
        // Validate player_id
        $playerIdValidation = self::validateInteger($data['player_id'] ?? '', 'Player ID', 1);
        if (!$playerIdValidation['valid']) {
            $errors[] = $playerIdValidation['message'];
        } else {
            $sanitized['player_id'] = $playerIdValidation['value'];
        }
        
        // Validate event_type
        $allowedEventTypes = ['goal', 'assist', 'yellow_card', 'red_card', 'substitution'];
        $eventTypeValidation = self::validateEnum($data['event_type'] ?? '', 'Event type', $allowedEventTypes);
        if (!$eventTypeValidation['valid']) {
            $errors[] = $eventTypeValidation['message'];
        } else {
            $sanitized['event_type'] = $eventTypeValidation['value'];
        }
        
        // Validate minute
        $minuteValidation = self::validateInteger($data['minute'] ?? '', 'Minute', 1, 120);
        if (!$minuteValidation['valid']) {
            $errors[] = $minuteValidation['message'];
        } else {
            $sanitized['minute'] = $minuteValidation['value'];
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $sanitized
        ];
    }
    
    // Validate and sanitize player data
    public static function validatePlayerData($data) {
        $errors = [];
        $sanitized = [];
        
        // Validate first name
        $firstNameValidation = self::validateRequiredString($data['first_name'] ?? '', 'First name', 2, 50);
        if (!$firstNameValidation['valid']) {
            $errors[] = $firstNameValidation['message'];
        } else {
            $sanitized['first_name'] = self::sanitizeString($firstNameValidation['value']);
        }
        
        // Validate last name
        $lastNameValidation = self::validateRequiredString($data['last_name'] ?? '', 'Last name', 2, 50);
        if (!$lastNameValidation['valid']) {
            $errors[] = $lastNameValidation['message'];
        } else {
            $sanitized['last_name'] = self::sanitizeString($lastNameValidation['value']);
        }
        
        // Validate position
        $positionValidation = self::validateRequiredString($data['position'] ?? '', 'Position', 2, 50);
        if (!$positionValidation['valid']) {
            $errors[] = $positionValidation['message'];
        } else {
            $sanitized['position'] = self::sanitizeString($positionValidation['value']);
        }
        
        // Validate team_id (optional)
        if (!empty($data['team_id'])) {
            $teamIdValidation = self::validateInteger($data['team_id'], 'Team ID', 1);
            if (!$teamIdValidation['valid']) {
                $errors[] = $teamIdValidation['message'];
            } else {
                $sanitized['team_id'] = $teamIdValidation['value'];
            }
        } else {
            $sanitized['team_id'] = null;
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $sanitized
        ];
    }
    
    // Validate and sanitize team data
    public static function validateTeamData($data) {
        $errors = [];
        $sanitized = [];
        
        // Validate team name
        $teamNameValidation = self::validateRequiredString($data['team_name'] ?? '', 'Team name', 2, 100);
        if (!$teamNameValidation['valid']) {
            $errors[] = $teamNameValidation['message'];
        } else {
            $sanitized['team_name'] = self::sanitizeString($teamNameValidation['value']);
        }
        
        // Validate city (optional)
        if (!empty($data['city'])) {
            $cityValidation = self::validateRequiredString($data['city'], 'City', 2, 100);
            if (!$cityValidation['valid']) {
                $errors[] = $cityValidation['message'];
            } else {
                $sanitized['city'] = self::sanitizeString($cityValidation['value']);
            }
        } else {
            $sanitized['city'] = null;
        }
        
        // Validate coach name (optional)
        if (!empty($data['coach_name'])) {
            $coachValidation = self::validateRequiredString($data['coach_name'], 'Coach name', 2, 100);
            if (!$coachValidation['valid']) {
                $errors[] = $coachValidation['message'];
            } else {
                $sanitized['coach_name'] = self::sanitizeString($coachValidation['value']);
            }
        } else {
            $sanitized['coach_name'] = null;
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $sanitized
        ];
    }
} 