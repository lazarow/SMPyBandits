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

function deleteDir($path) {
    if (! is_dir($path)) {
        throw new InvalidArgumentException("$path must be a directory");
    }
    if (substr($path, strlen($path) - 1, 1) != '/') {
        $path .= '/';
    }
    $files = glob($path . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($path);
}
