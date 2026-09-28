<?php
/**
 * Alert Component
 * Usage: 
 *   showSuccess('Operation completed successfully');
 *   showError('Something went wrong');
 *   showWarning('Please check your input');
 *   showInfo('New feature available');
 */

function showAlert($type, $message, $dismissible = true) {
    $alertClasses = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'danger' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];
    
    $iconClasses = [
        'success' => 'fa-check-circle',
        'error' => 'fa-exclamation-circle',
        'danger' => 'fa-exclamation-circle',
        'warning' => 'fa-exclamation-triangle',
        'info' => 'fa-info-circle'
    ];
    
    $class = $alertClasses[$type] ?? 'alert-info';
    $icon = $iconClasses[$type] ?? 'fa-info-circle';
    $dismissBtn = $dismissible ? '<button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>' : '';
    
    return <<<HTML
    <div class="alert {$class}">
        <i class="fas {$icon}"></i>
        <span>{$message}</span>
        {$dismissBtn}
    </div>
    HTML;
}

function showSuccess($message, $dismissible = true) {
    return showAlert('success', $message, $dismissible);
}

function showError($message, $dismissible = true) {
    return showAlert('error', $message, $dismissible);
}

function showWarning($message, $dismissible = true) {
    return showAlert('warning', $message, $dismissible);
}

function showInfo($message, $dismissible = true) {
    return showAlert('info', $message, $dismissible);
}

// Display alert directly (echo)
function displayAlert($type, $message, $dismissible = true) {
    echo showAlert($type, $message, $dismissible);
}

function displaySuccess($message, $dismissible = true) {
    echo showSuccess($message, $dismissible);
}

function displayError($message, $dismissible = true) {
    echo showError($message, $dismissible);
}

function displayWarning($message, $dismissible = true) {
    echo showWarning($message, $dismissible);
}

function displayInfo($message, $dismissible = true) {
    echo showInfo($message, $dismissible);
}
?>