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

    protected static $skip_image_filters = false;

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

      add_filter( 'upload_dir', array( &$this, 'filter_upload_dir' ) );
      add_filter( 'pre_option_uploads_use_yearmonth_folders', '__return_null' );
      // add_filter( 'plupload_init', array( &$this, 'plupload_init' ) );
      add_filter('wp_handle_upload ', 'custom_upload_filter' );
      add_filter( 'wp_handle_upload_prefilter', array( &$this, 'upload_prefilter' ) );
    }

    function upload_prefilter( $file ) {
      if ( ! get_option( 'minio_unique_filename' ) )
        return $file;

      $file_info  = pathinfo( $file['name'] );
      $file_hash  = crc32( json_encode( array( $file_info['basename'], current_time( 'mysql' ) ) ) );
      $file_time  = date( 'Ymd', current_time( 'timestamp', 0 ) );
      $file_ext   = $file_info['extension'];
      $file['name'] = "$file_time-$file_hash.$file_ext";

      return $file;
    }

    function filter_upload_dir( $param ) {
      if ( self::$skip_image_filters ) {
        return $param;
      }

      $param = array(
        'path'    => $this->get_s3path(),
        'url'     => $this->get_url(),
        'basedir' => $this->get_s3path(),
        'baseurl' => $this->get_url(),
        'subdir'  => '',
        'error'   => false
      );

      return $param;
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

      $this->s3client->registerStreamWrapper();

      // $data = file_get_contents('s3://padaphant/20101205-081245.jpg');
      // echo $data;

      return $this->s3client;
    }

    function get_url() {
      return trailingslashit( $this->endpoint ) . $this->bucket;
    }

    function get_s3path() {
      return "s3://$this->bucket";
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
