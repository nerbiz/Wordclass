<?php

namespace Nerbiz\WordClass;

class Init
{
    /**
     * The default prefix to use
     * @var string
     */
    protected static $prefix = 'nw';

    /**
     * The path to the vendor directory
     * @var string
     */
    protected static $vendorPath;

    /**
     * The URI to the vendor directory
     * @var string
     */
    protected static $vendorUri;

    /**
     * Set the prefix to use for various things
     * @param string $prefix
     * @return void
     */
    public static function setPrefix(string $prefix): void
    {
        static::$prefix = $prefix;
    }

    /**
     * @return string
     */
    public static function getPrefix(): string
    {
        return static::$prefix;
    }

    /**
     * @param string $vendorPath
     * @return void
     */
    public static function setVendorPath(string $vendorPath): void
    {
        static::$vendorPath = rtrim($vendorPath, '/') . '/';
    }

    /**
     * Get the vendor path, optionally appended with an extra path
     * @param  string|null $path
     * @return string
     */
    public static function getVendorPath(?string $path = null): string
    {
        // The default value is the 'vendor' directory in a (child-)theme directory
        if(static::$vendorPath === null) {
            static::setVendorPath(get_stylesheet_directory() . '/vendor/');
        }

        return static::$vendorPath . $path;
    }

    /**
     * @param string $vendorUri
     * @return void
     */
    public static function setVendorUri(string $vendorUri): void
    {
        static::$vendorUri = rtrim($vendorUri, '/') . '/';
    }

    /**
     * Get the vendor URI, optionally appended with an extra path
     * @param  string|null $path
     * @return string
     */
    public static function getVendorUri(?string $path = null): string
    {
        // The default value is the 'vendor' directory in a (child-)theme directory
        if (static::$vendorUri === null) {
            static::setVendorUri(get_stylesheet_directory_uri() . '/vendor/');
        }

        return static::$vendorUri . $path;
    }

    /**
     * Define some useful constants
     * @return self
     */
    public function defineConstants(): self
    {
        // The absolute paths to the template/stylesheet directory
        define(
            strtoupper(static::$prefix) . '_THEME_PATH',
            get_template_directory() . '/'
        );

        define(
            strtoupper(static::$prefix) . '_TEMPLATE_PATH',
            constant(strtoupper(static::$prefix) . '_THEME_PATH')
        );

        define(
            strtoupper(static::$prefix) . '_STYLESHEET_PATH',
            get_stylesheet_directory() . '/'
        );


        // The URI paths to the template/stylesheet directory
        define(
            strtoupper(static::$prefix) . '_THEME_URI',
            get_template_directory_uri() . '/'
        );

        define(
            strtoupper(static::$prefix) . '_TEMPLATE_URI',
            constant(strtoupper(static::$prefix) . '_THEME_URI')
        );

        define(
            strtoupper(static::$prefix) . '_STYLESHEET_URI',
            get_stylesheet_directory_uri() . '/'
        );

        return $this;
    }

    /**
     * Load translation files
     * @return self
     */
    public function loadTranslations(): self
    {
        load_theme_textdomain(
            'wordclass',
            dirname(__FILE__, 2) . '/includes/languages'
        );

        return $this;
    }

    /**
     * Include the functions file for convenience
     * @return self
     */
    public function includeHelperFunctions(): self
    {
        require_once __DIR__ . '/../includes/php/helper-functions.php';

        return $this;
    }
}
