<?php

declare(strict_types=1);

namespace App\ControllerFunctions\Install;

class RequirementsChecker
{
    /**
     * Minimum PHP Version Supported (Override is in installer.php config file).
     */
    private string $_minPhpVersion = '7.2.0';

    /**
     * Check for the server requirements.
     *
     * @param array<string, string> $requirements
     *
     * @return array<string, bool>
     */
    public function check(array $requirements): array
    {
        $results = [];
        foreach ($requirements as $type => $requirement_) {
            switch ($type) {
                // check php requirements
                case 'php':
                    foreach ($requirements[$type] as $requirement) {
                        $results['requirements'][$type][$requirement] = true;
                        if (!\extension_loaded($requirement)) {
                            // @codeCoverageIgnoreStart
                            $results['requirements'][$type][$requirement]
                                = false;
                            $results['errors'] = true;
                            // @codeCoverageIgnoreEnd
                        }
                    }

                    if ($this->checkExec()) {
                        $results['requirements'][$type]['Php exec() available'] = true;
                    } else {
                        // @codeCoverageIgnoreStart
                        $results['requirements'][$type]['Php exec() not available (optional)'] = false;
                        // @codeCoverageIgnoreEnd
                    }

                    break;
                // check apache requirements
                case 'apache':
                    foreach ($requirements[$type] as $requirement) {
                        // if function doesn't exist we can't check apache modules
                        // @codeCoverageIgnoreStart
                        if (\function_exists('apache_get_modules')) {
                            $results['requirements'][$type][$requirement]
                                = true;
                            if (!\in_array($requirement, \apache_get_modules(), true)) {
                                $results['requirements'][$type][$requirement]
                                    = false;
                                $results['errors'] = true;
                            }
                        }
                        // @codeCoverageIgnoreEnd
                    }
                    break;

                // @codeCoverageIgnoreStart
                default:
                    break;
                // @codeCoverageIgnoreEnd
            }
        }

        return $results;
    }

    /**
     * Check PHP version requirement.
     *
     * @return array<string>
     */
    public function checkPHPversion(?string $minPhpVersion = null): array
    {
        $minVersionPhp = $minPhpVersion;
        $currentPhpVersion = $this->getPhpVersionInfo();
        $supported = false;
        if ($minPhpVersion === null) {
            // @codeCoverageIgnoreStart
            $minVersionPhp = $this->getMinPhpVersion();
            // @codeCoverageIgnoreEnd
        }
        if (\version_compare($currentPhpVersion['version'], $minVersionPhp)
            >= 0
        ) {
            $supported = true;
        }

        return [
            'full' => $currentPhpVersion['full'],
            'current' => $currentPhpVersion['version'],
            'minimum' => $minVersionPhp,
            'supported' => $supported,
        ];
    }

    /**
     * Check if exec is enabled. This will allow us to execute the migration.
     */
    public function checkExec(): bool
    {
        $disabled = \explode(',', \ini_get('disable_functions'));

        return !\in_array('exec', $disabled, true);
    }

    /**
     * Get current Php version information.
     *
     * @return array<string>
     */
    private static function getPhpVersionInfo(): array
    {
        $currentVersionFull = PHP_VERSION;
        \preg_match("#^\d+(\.\d+)*#", $currentVersionFull, $filtered);
        $currentVersion = $filtered[0];

        return [
            'full' => $currentVersionFull,
            'version' => $currentVersion,
        ];
    }

    /**
     * Get minimum PHP version ID.
     *
     * @return string _minPhpVersion
     */
    // @codeCoverageIgnoreStart
    protected function getMinPhpVersion(): string
    {
        return $this->_minPhpVersion;
    }

    // @codeCoverageIgnoreEnd
}
