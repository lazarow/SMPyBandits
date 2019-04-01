<?php
/**
 * [!] errors
 * [#] warnings
 * [i] info
 * [_] debug
 */

$options = getopt('c:e:', ['force']);

function getConfiguration()
{
    global $options;
    $configurationFilename = __DIR__ . '/configurations/'
        . (isset($options['c']) ? $options['c'] . '.php' : 'default.php');
    if (file_exists($configurationFilename) === false) {
        exit('[!] The configuration file ' . basename($configurationFilename) . ' does not exist');
    }
    return array_merge(
        include __DIR__ . '/configurations/default.php',
        include $configurationFilename
    );
}

function getExperiment()
{
    global $options;
    if (isset($options['e']) === false) {
        exit('[!] The experiment\'s name is not provided');
    }
    $experimentFilename = __DIR__ . '/experiments/'. $options['e'] . '.php';
    if (file_exists($experimentFilename) === false) {
        exit('[!] The experiment file ' . basename($experimentFilename) . ' does not exist');
    }
    return include $experimentFilename;
}
