<?php
/**
 *	Kalium WordPress Theme
 *	
 *	Laborator.co
 *	www.laborator.co 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Helpers {
	
	/**
	 *	Admin notices to show
	 */
	private static $admin_notices = array();
	
	
	public function __construct() {
		$this->admin_init_priority = 1000;
	}
	
	/**
	 *	Execute admin actions
	 *
	 *	@type action
	 */
	public function admin_init() {
		// Show defined admin notices
		if ( count( self::$admin_notices ) ) {
			add_action( 'admin_notices', array( & $this, 'showAdminNotices' ), 1000 );
		}
	}
	
	/**
	 *	Add admin notice
	 */
	public static function addAdminNotice( $message, $type = 'success', $dismissible = true ) {
		
		switch ( $type ) {
			case 'success':
			case 'error':
			case 'warning':
				break;
			
			default:
				$type = 'info';
		}
		
		self::$admin_notices[] = array( 
			'message'        => $message,
			'type'           => $type,
			'dismissible'    => $dismissible ? true : false
		);
	}
	
	/**
	 *	Let to Num
	 */
	public static function letToNum( $size ) {
		$l   = substr( $size, -1 );
		$ret = substr( $size, 0, -1 );
		switch ( strtoupper( $l ) ) {
			case 'P':
				$ret *= 1024;
			case 'T':
				$ret *= 1024;
			case 'G':
				$ret *= 1024;
			case 'M':
				$ret *= 1024;
			case 'K':
				$ret *= 1024;
		}
		return $ret;
	}
	
	/**
	 *	Show defined admin notices
	 */
	public function showAdminNotices() {
		foreach ( self::$admin_notices as $i => $notice ) {
			?>			
			<div class="laborator-notice notice notice-<?php echo $notice['type']; echo $notice['dismissible'] ? ' is-dismissible' : ''; ?>">
				<?php echo wpautop( $notice['message'] ); ?>
			</div>
			<?php
		}
		
	}
	
	/**
	 *	Get SVG dimensions from viewBox
	 */
	public function getSVGDimensions( $file ) {
		$width = $height = 1;
		
		// Get attached file
		if ( is_numeric( $file ) ) {
			$file = get_attached_file( $file );
		}
		
		if ( function_exists( 'simplexml_load_file' ) ) {
			$svg = simplexml_load_file( $file );
			
			if ( isset( $svg->attributes()->viewBox ) ) {
				$view_box = explode( ' ', (string) $svg->attributes()->viewBox );
				$view_box = array_values( array_filter( array_map( 'absint', $view_box ) ) );
				
				if ( count( $view_box ) > 1 ) {
					return array( $view_box[0], $view_box[1] );
				}
			}
		}
		
		return array( $width, $height );
	}
	
	/**
	 *	Safe JSON for numeric checks
	 */
	public function safeEncodeJSON( $arr ) {
		// Check for older version of php
		if ( function_exists( 'phpversion' ) && version_compare( phpversion(), '5.3.3', '<' ) ) {
			return json_encode( $arr );
		}
		
		return json_encode( $arr, JSON_NUMERIC_CHECK );
	}
	
	/**
	 *	Add Body Class
	 */
	public function addBodyClass( $classes = '' ) {
		if ( ! is_array( $classes ) ) {
			$classes = explode( ' ', $classes );
		}
		
		$classes = array_map( 'esc_attr', $classes );
		
		add_filter( 'body_class', create_function( '$classes', '$classes[] = "' . implode( ' ', $classes ) . '"; return $classes;' ) );
	}
	
	/**
	 *	Show CSS classes attributes
	 */
	public function showClasses( $classes, $echo = false ) {
		if ( ! is_array( $classes ) ) {
			$classes = array( $classes );
		}
		
		$classes = implode( ' ', array_map( 'esc_attr', $classes ) );
		
		if ( $echo ) {
			echo $classes;
		}
		
		return $classes;
	}
	
	/**
	 *	Check if file is SVG extension
	 */
	public function isSVG( $file ) {
		$file_info = pathinfo( $file );
		return 'SVG' == strtoupper( get_array_key( $file_info, 'extension' ) );
	}
	
	/**
	 *	Active Plugins
	 */
	public function isPluginActive( $plugin ) {
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );
		$active_sitewide_plugins = apply_filters( 'active_sitewide_plugins', get_site_option( 'active_sitewide_plugins', array() ) );
		$plugins = array_merge( $active_plugins, $active_sitewide_plugins );
		
		#print_r( $plugins );
		
		return in_array( $plugin, $plugins ) || isset( $plugins[ $plugin ] );
	}
	
	/**
	 * Check if given URL is YouTube
	 */
	public function isYouTube( $url ) {
		return preg_match( '#^https?://(?:www\.)?(?:youtube\.com/watch|youtu\.be/)#', $url );
	}
	
	/**
	 * Check if given URL is Vimeo
	 */
	public function isVimeo( $url ) {
		return preg_match( '#^https?://(.+\.)?vimeo\.com/.*#', $url );
	}
	
	/**
	 * Check if given URL is a video
	 */
	public function isVideo( $url ) {
		return $this->isYouTube( $url ) || $this->isVimeo( $url );
	}
}