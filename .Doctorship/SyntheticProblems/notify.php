<?php
require __DIR__ . '/_common.php';
$configuration = getConfiguration();
$experiment = getExperiment();

$success = mail(
    $configuration['notifications.email'],
    'The experiment: ' . $experiment['name'] . ' has been finished',
    '[' . date('Y-m-d H:i:s') . '] The experiment: ' . $experiment['name'] . ' has been finished.',
    [
        'From' => 'automail@doktorat.vps',
        'Reply-To' => 'automail@doktorat.vps',
        'X-Mailer' => 'PHP/' . phpversion()
    ]
);
if (! $success) {
    echo error_get_last()['message'];
}
