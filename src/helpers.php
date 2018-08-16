<?php

/**
 * Get the path that the user has run the script from
 *
 * @param  string|null $path
 * @return string
 */
function runPath(string $path = null) : string
{
    $dir = FRB_RUN_PATH;

    return $path ? "$dir/$path" : $dir;
}

/**
 * Get the path to the frb-cli installation root
 *
 * @param  string|null $path
 * @return string
 */
function frbCliPath(string $path = null) : string
{
    $dir = FRB_CLI_PATH;

    return $path ? "$dir/$path" : $dir;
}

/**
 * Get the .deploy env path
 *
 * @param  string|null $path
 * @return string
 */
function frbEnvPath(string $path = null) : string
{
    $dir = rootPath('.deploy');

    return $path ? "$dir/$path" : $dir;
}

/**
 * Find the project root
 *
 * @return string
 */
function rootPath(string $path = null)  : string
{
    $currentDir = runPath();

    while ($currentDir) {
        if ($currentDir === '/') {
            break;
        }

        if (is_dir(sprintf('%s/.deploy', $currentDir))) {
            return $path ? "$currentDir/$path" : $path;
        }

        $currentDir = dirname($currentDir);
    }

    throw new \Exception('Unable to find a root directory with a .deploy folder');
}
