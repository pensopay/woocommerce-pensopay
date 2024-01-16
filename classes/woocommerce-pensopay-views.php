<?php

/**
 * Class WC_PensoPay_Views
 */
class WC_PensoPay_Views
{
    /**
     * Fetches and shows a view
     *
     * @param string $path
     * @param array $args
     */
    public static function get_view( $path, $args = [] )
    {
        if (is_array($args) && ! empty($args)) {
            extract($args);
        }

        $file = WCPP_PATH . 'views/' . trim($path);

        if (file_exists($file)) {
            include $file;
        }
    }

	/**
	 * @param $path
	 *
	 * @return string
	 */
	public static function asset_url( $path ): string {
		wc_deprecated_function( __METHOD__, '7.0.0', 'Will be removed' );

		return WC_PP()->plugin_url( 'assets/' . $path );
	}
}