<?php

final class Minio {

    private $s3client;
    private $access_key;
    private $secret_key;
    private $bucket;

    private $settings_title;
    private $settings_menu_title;

    static $settings_page;
    static $slug = 'minio';
    static $version;

    function __construct( $plugin_file_path, $version = null, $slug = null ) {
      if ( ! is_null( $slug ) ) {
        self::$slug = $slug;
      }

      if ( ! is_null( $version ) ) {
        self::$version = $version;
      }

      $this->init( $plugin_file_path );
    }
 
    function init( $plugin_file_path ) {
      self::$settings_page         = self::$slug;
      $this->settings_title        = __( 'Minio', self::$slug );
      $this->settings_menu_title   = __( 'Minio', self::$slug );
    
      $settings = new Minio_Settings();

      if ( ! get_option( 'minio_access_key' )
        || ! get_option( 'minio_secret_key' )
        || ! get_option( 'minio_endpoint' )
        || ! get_option( 'minio_bucket' ) )
        return;
      
      $this->access_key   = get_option( 'minio_access_key' );
      $this->secret_key   = get_option( 'minio_secret_key' );
      $this->endpoint     = get_option( 'minio_endpoint' );
      $this->bucket       = get_option( 'minio_bucket' );

      global $minio;
      $this->get_s3client();
      $minio = $this;

      $this->filter_local   = new Minio_Local_To_S3( $this );
      $this->filter_s3      = new Minio_S3_To_Local( $this );

      add_filter( 'wp_handle_upload_prefilter', array( &$this, 'wp_handle_upload_prefilter' ), 1 );
		  add_filter( 'wp_handle_sideload_prefilter', array( &$this, 'wp_handle_upload_prefilter' ), 1 );
		  add_filter( 'wp_update_attachment_metadata', array( &$this, 'wp_update_attachment_metadata' ), 110, 2 );
		  add_filter( 'delete_attachment', array( &$this, 'delete_attachment' ), 20 );
		  add_filter( 'update_attached_file', array( &$this, 'update_attached_file' ), 100, 2 ); 
    }

    function wp_handle_upload_prefilter( $file ) {
		  // Get Post ID if uploaded in post screen.
		  $post_id = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );
		  $file['name'] = $this->filter_unique_filename( $file['name'], $post_id );
		  return $file;
	  }

    function filter_unique_filename( $filename, $post_id = null ) {
		
      if ( ! $this->get_option( 'minio_copy_to_s3' ) ) {
			  return $filename;
		  }

      // sanitize the file name before we begin processing
      $filename = sanitize_file_name( $filename );
      // Get base filename without extension.
      $ext  = pathinfo( $filename, PATHINFO_EXTENSION );
      $ext  = $ext ? ".$ext" : '';
      $name = wp_basename( $filename, $ext );
      // Edge case: if file is named '.ext', treat as an empty name.
      if ( $name === $ext ) {
        $name = '';
      }
      // Rebuild filename with lowercase extension as S3 will have converted extension on upload.
      $ext      = strtolower( $ext );
      $filename = $name . $ext;
      $time     = current_time( 'mysql' );
      // Get time if uploaded in post screen.
      if ( ! empty( $post_id ) ) {
        $time = $this->get_post_time( $post_id );
      }
      if ( ! $this->does_file_exist( $filename, $time ) ) {
        // File doesn't exist locally or on S3, return it.
        return $filename;
      }
      $filename = $this->generate_unique_filename( $name, $ext, $time );
      
      return $filename;
	  }

    function generate_unique_filename( $name, $ext, $time ) {
		  $count    = 1;
		  $filename = $name . $count . $ext;
		  while ( $this->does_file_exist( $filename, $time ) ) {
			  $count++;
			  $filename = $name . $count . $ext;
		  }
		  return $filename;
	  }

    function get_s3client() {
      if ( is_null ( $this->s3client ) ) {
        $args = array(
          'version'   => 'latest',
          'region'    => 'us-east-1',
          'endpoint'  => $this->endpoint,
          'use_path_style_endpoint' => true,
          'credentials' => [
            'key'    => $this->access_key,
            'secret' => $this->secret_key
          ],
        );
        $this->set_s3client( new Aws\S3\S3Client( $args ) );
      }
      
      return $this->s3client;
    }

    function does_file_exist_s3( $filename, $time ) {
		  $s3client = $this->get_s3client();
		  return $s3client->doesObjectExist( $bucket, $filename );
	  }

    function set_s3client( $s3client ) {
      $this->s3client = $s3client;
    }

    static function activation() {
      return;
    }

    protected function __clone() {

    }
}
