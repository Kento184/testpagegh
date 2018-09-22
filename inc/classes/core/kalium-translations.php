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

class Kalium_Translations {
	
	/**
	 * Translations repot
	 */
	private $repository = 'https://api.github.com/repos/arl1nd/Kalium-Translations';
	
	/**
	 * If there is a Kalium translation being loaded
	 */
	private $has_translation = false;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'load_textdomain', array( $this, 'loadTextDomain' ), 10, 2 );
	}
	
	/**
	 * Admin page hook
	 *
	 * @type action
	 */
	public function admin_init() {
		
		if ( ! $this->has_translation ) {
			// Check for existing remote translation
			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'checkExistingTranslation' ), 100 );
			add_filter( 'pre_set_transient_update_themes', array( $this, 'checkExistingTranslation' ), 100 );
		} else {
			// Check for translation updates
			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'checkTranslationUpdates' ), 100 );
			add_filter( 'pre_set_transient_update_themes', array( $this, 'checkTranslationUpdates' ), 100 );
		}
	}
	
	/**
	 * Load text domain
	 *
	 * @type action
	 */
	public function loadTextDomain( $domain, $mofile ) {
		
		if ( 'kalium' == $domain && file_exists( $mofile ) ) {
			$this->has_translation = true;
		}
	}
	
	/**
	 * Check for existing translation of current language
	 */
	public function checkExistingTranslation( $transient ) {

		$translation = $this->getRemoteTranslation();
		
		if ( ! is_null( $translation ) ) {
			$translation_update = array(
				'type'      => 'theme',
				'slug'      => 'kalium',
				'language'  => get_locale(), // Current language
				'package'   => $translation->download_url,
				'version'	=> kalium()->getVersion(),
				'updated'	=> date( 'Y-m-d H:i:s' )
			);
			
			$transient->translations[] = $translation_update;
		}
		
		return $transient;
	}
	
	/**
	 * Check for translation updates
	 */
	public function checkTranslationUpdates( $transient ) {
		global $l10n;
		
		$kalium_l10n = get_array_key( $l10n, 'kalium' );
		
		if ( $kalium_l10n && ! empty( $kalium_l10n->headers['X-Translation-Version'] ) ) {
			$translation_locale = get_locale();
			
			$current_translation_version = $kalium_l10n->headers['X-Translation-Version'];
			$remote_translation_version = $this->getRemoteTranslationVersion();
			
			if ( $remote_translation_version && version_compare( $remote_translation_version, $current_translation_version, '>' ) ) {
				$package_url = sprintf( 'https://raw.githubusercontent.com/arl1nd/Kalium-Translations/master/%s.zip', $translation_locale );
				
				$translation_update = array(
					'type'       => 'theme',
					'slug'       => 'kalium',
					'language'   => $translation_locale,
					'package'    => $package_url,
					'version'	 => $remote_translation_version,
					//'autoupdate' => 1
				);
				
				$transient->translations[] = $translation_update;
			}
		}
		
		return $l10n;
	}
	
	/**
	 * Get avaialble translation for current locale
	 *
	 * @return (object) $response (name, path, sha, size, url, html_url, git_url, download_url, type, content, encoding, _links)
	 */
	private function getRemoteTranslation() {
		$locale = get_locale();
		$contents_url = sprintf( '%s/contents/%s.zip', $this->repository, esc_attr( $locale ) );
		
		$request = wp_remote_get( $contents_url );
		$request_body = wp_remote_retrieve_body( $request );
		
		if ( ! is_wp_error( $request ) ) {
			$response = json_decode( $request_body );
			
			if ( ! empty( $response->download_url ) ) {
				return $response;
			}
		}
		
		return null;
	}
	
	/**
	 * Get remote translation locale version
	 *
	 * @return (string) $version
	 */
	private function getRemoteTranslationVersion() {
		$locale = get_locale();
		$contents_url = sprintf( '%1$s/contents/%2$s/%2$s.po', $this->repository, esc_attr( $locale ) );
		
		$request = wp_remote_get( $contents_url );
		$request_body = wp_remote_retrieve_body( $request );
		
		if ( ! is_wp_error( $request ) ) {
			$response = json_decode( $request_body );
			
			if ( ! empty( $response->content ) ) {
				$content = base64_decode( $response->content );
				
				// Get translation version
				if ( preg_match( '#X-Translation-Version:\s*([0-9\.]+)#', $content, $matches ) ) {
					return $matches[1];
				}
			}
		}
		
		return null;
	}
}