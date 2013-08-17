<?php
/**
 * Name: WP-CLI Integration
 *
 * Description: Adds WP-CLI Integration for the Pods API
 *
 * Version: 1.0
 *
 * Category: Integration
 *
 * Class: PodsComponent_WP_CLI
 */
class PodsComponent_WP_CLI extends PodsComponent {

    static $component_path;

    static $component_file;

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 1.0
     */
    public function __construct () {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            include_once self::$component_path . 'Pods_Command.php';
            include_once self::$component_path . 'PodsAPI_Command.php';
        }
    }

    /**
     * Register the component
     *
     * @param $components
     *
     * @return array
     * @since 1.0
     */
    public static function component_register ( $components ) {
        $components[] = array( 'File' => realpath( self::$component_file ) );

        return $components;
    }
}