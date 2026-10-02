<?php

/**
 * Database Configuration Example
 *
 * GitHub-safe configuration file.
 *
 * For local XAMPP:
 * 1. Copy this file as db.php
 * 2. Update the database credentials if required.
 *
 * Never commit real database passwords to GitHub.
 */

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'msp';
$DB_PORT = 3306;

/*
 * Create the database connection.
 */
$conn = new mysqli(
    $DB_HOST,
    $DB_USER,
    $DB_PASS,
    $DB_NAME,
    $DB_PORT
);

/*
 * Check connection.
 */
if ($conn->connect_error) {
    die("Database connection failed.");
}

/*
 * Set UTF-8 character encoding.
 */
$conn->set_charset("utf8mb4");