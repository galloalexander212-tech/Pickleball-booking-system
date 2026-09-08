<?php
/* =====================================
   FORM VALIDATION
===================================== */

class Validator {

    public $errors = [];

    /* Required field */
    function checkEmpty($value, $field, $label) {
        if (trim($value) === "") {
            $this->errors[$field] = $label . " is required.";
        }
    }

    /* Username: 3-20 letters, numbers, underscores */
    function validUsername($value, $field) {
        if (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $value)) {
            $this->errors[$field] = "Username must be 3-20 characters (letters, numbers and underscores only).";
        }
    }

    /* Email format */
    function validEmail($value, $field) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "Please enter a valid email address.";
        }
    }

    /* Password: minimum 8 characters */
    function validPassword($value, $field) {
        if (strlen($value) < 8) {
            $this->errors[$field] = "Password must be at least 8 characters.";
        }
    }

    /* Password and confirm must match */
    function passwordMatch($password, $confirm, $field) {
        if ($password !== $confirm) {
            $this->errors[$field] = "Passwords do not match.";
        }
    }

    /* Any errors so far? */
    function hasErrors() {
        return !empty($this->errors);
    }
}
?>