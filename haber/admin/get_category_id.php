<?php
require_once '../include/functions.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get category name from request
$category_name = isset($_GET['name']) ? trim($_GET['name']) : '';

// If no category name provided, return error
if (empty($category_name)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No category name provided']);
    exit();
}

// Get category ID
$categoryObj = new Category();
$category = $categoryObj->getByName($category_name);

// Return result
header('Content-Type: application/json');
if ($category) {
    echo json_encode(['id' => $category['id'], 'name' => $category['name']]);
} else {
    echo json_encode(['error' => 'Category not found']);
}
?> 