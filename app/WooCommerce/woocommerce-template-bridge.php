<?php

defined('ABSPATH') || exit;

if (! function_exists('view')) {
    return;
}

$rawTemplateName = null;
if (isset($template_name) && is_string($template_name) && $template_name !== '') {
    $rawTemplateName = $template_name;
} elseif (isset($GLOBALS['flux_press_wc_blade_template_name']) && is_string($GLOBALS['flux_press_wc_blade_template_name'])) {
    $rawTemplateName = $GLOBALS['flux_press_wc_blade_template_name'];
}

if (! is_string($rawTemplateName) || $rawTemplateName === '') {
    return;
}

$relativeTemplate = trim(str_replace('\\', '/', $rawTemplateName), '/');
if ($relativeTemplate === '' || ! str_ends_with($relativeTemplate, '.php')) {
    return;
}

$viewName = 'woocommerce.' . str_replace('/', '.', substr($relativeTemplate, 0, -4));
if (! view()->exists($viewName)) {
    return;
}

$viewData = [];
if (isset($action_args['args']) && is_array($action_args['args'])) {
    $viewData = $action_args['args'];
} elseif (isset($args) && is_array($args)) {
    $viewData = $args;
}

echo view($viewName, $viewData)->render();
