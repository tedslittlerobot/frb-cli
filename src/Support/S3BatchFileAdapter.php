<?php

namespace Tlr\Frb\Support;

use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Config;
use Symfony\Component\Finder\Finder;

class S3BatchFileAdapter extends AwsS3Adapter
{
    public function write($path, $contents, Config $config)
    {
        if (!$config->has('local-path')) {
            return parent::write($path, $contents, $config);
        }

        if (!is_dir($config->get('local-path'))) {
            return parent::write($path, $contents, $config);
        }

        $finder = new Finder;

        $finder->files()->in($config->get('local-path'));

        $output = [];

        foreach ($finder as $file) {
            $from = '/' . path_fragments($config->get('local-path'), $file->getRelativePathname());
            $to = path_fragments($path, $file->getRelativePathname());

            $fileConfig = (new Config)->setFallback($config);

            if (ends_with($to, '.css')) {
                $fileConfig->set('mimetype', 'text/css');
            }

            if (ends_with($to, '.svg')) {
                $fileConfig->set('mimetype', 'image/svg+xml');
            }

            $output[] = parent::write(
                $to,
                file_get_contents($from),
                $fileConfig
            );
        }

        return $output;
    }
}
