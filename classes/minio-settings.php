<?php

final class Minio_Settings{

  function __construct() {
    add_action( 'admin_menu', array( &$this, 'add_settings_page' ) );
    add_action( 'admin_init', array( &$this, 'register_settings' ) );
		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
    add_action( 'admin_enqueue_scripts',array( &$this, 'admin_scripts' ) );
	}

  public function admin_scripts() {
    wp_register_style( 'minio_admin_style', MINIO__PLUGIN_URL . 'admin/admin.css', false, Minio::$version );
    wp_register_script( 'minio_admin_script', MINIO__PLUGIN_URL . 'admin/admin.js', array( 'jquery' ), Minio::$version, true );
    
    wp_enqueue_style( 'minio_admin_style' );
		wp_enqueue_script( 'minio_admin_script' );
  }

  public function register_settings() {
    $args = array(
      'id'			    => 'minio_credentials',
			'title'			  => __( 'Credentials', Minio::$slug ),
			'page'			  => 'minio_settings_page',
			'description'	=> __( 'Please, enter your Minio credentials here.', Minio::$slug ),
    );
    $credentials = new Minio_Settings_Section( $args );

    $args = array(
			'id'				    => 'minio_access_key',
			'title'				  => __( 'Access Key' ),
			'page'				  => 'minio_settings_page',
			'section'			  => 'minio_credentials',
			'description'	  => __( '' ),
			'type'				  => 'text', // text, textarea, password, checkbox
			'option_group'	=> 'settings_page_minio_settings_page',
		);
		$access_key = new Minio_Settings_Field( $args );

    $args = array(
			'id'				    => 'minio_secret_key',
			'title'				  => __( 'Secret' ),
			'page'				  => 'minio_settings_page',
			'section'			  => 'minio_credentials',
			'description'	  => __( '' ),
			'type'				  => 'text', // text, textarea, password, checkbox
			'option_group'	=> 'settings_page_minio_settings_page',
		);
		$secret_key = new Minio_Settings_Field( $args );

		$args = array(
			'id'				    => 'minio_endpoint',
			'title'				  => __( 'Endpoint' ),
			'page'				  => 'minio_settings_page',
			'section'			  => 'minio_credentials',
			'description'	  => __( '' ),
			'type'				  => 'text', // text, textarea, password, checkbox
			'option_group'	=> 'settings_page_minio_settings_page',
		);
		$endpoint = new Minio_Settings_Field( $args );

		$args = array(
			'id'				    => 'minio_bucket',
			'title'				  => __( 'Endpoint' ),
			'page'				  => 'minio_settings_page',
			'section'			  => 'minio_credentials',
			'description'	  => __( '' ),
			'type'				  => 'text', // text, textarea, password, checkbox
			'option_group'	=> 'settings_page_minio_settings_page',
		);
		$bucket = new Minio_Settings_Field( $args );

    $args = array(
      'id'			    => 'minio_settings',
			'title'			  => __( 'Settings', Minio::$slug ),
			'page'			  => 'minio_settings_page',
			'description'	=> __( 'These settings control some general settings of the plugin.', Minio::$slug ),
    );
    $settings = new Minio_Settings_Section( $args );

		$args = array(
			'id'				    => 'minio_unique_filename',
			'title'				  => __( 'Unique Filename' ),
			'page'				  => 'minio_settings_page',
			'section'			  => 'minio_settings',
			'description'	  => __( '' ),
			'type'				  => 'checkbox', // text, textarea, password, checkbox
			'option_group'	=> 'settings_page_minio_settings_page',
		);
		$unique_filename = new Minio_Settings_Field( $args );

  }

  public function add_settings_page() {
    $settings_page = add_options_page(
      __( 'Minio', Minio::$slug ),
      __( 'Minio', Minio::$slug ),
      'manage_options',
      'minio_settings_page',
      array( &$this, 'settings_page' )
    );
  }

  public function settings_page() {
    ?>
		<div class="wrap minio-settings-page">
			<h2><?php _e( 'Mino', Minio::$slug ); ?></h2>
			<form action="options.php" method="post">
			<?php
				global $wp_settings_sections, $wp_settings_fields;
				settings_fields('settings_page_minio_settings_page');
				$page = 'minio_settings_page';
			?>
			<div class="container-fluid settings-container">
				<div class="row container-row">
					<div class="col-xs-12 col-sm-4 col-md-3 navigation-container">
						<ul class="navigation">
						<?php
							if ( isset( $wp_settings_sections[$page] ) ) {
								foreach ( (array) $wp_settings_sections[$page] as $section ) {
									echo '<li class="nav-item">';
										echo '<a href="#'.$section['id'].'">';
											if($section['icon'])
												echo '<i class="fa fa-'.$section['icon'].'"></i> ';
											echo '<span class="hidden-xs">' . $section['title'] . '</span>';
										echo '</a>';
									echo '</li>';
								}
							}
						?>
						</ul>
					</div>
					<div class="col-xs-12 col-sm-8 col-md-9 content-container">
						<?php
							if ( isset( $wp_settings_sections[$page] ) ) {
								foreach ( (array) $wp_settings_sections[$page] as $section ) {
									echo '<div class="section" id="section-'.$section['id'].'">';
									if ( $section['icon'] ) {
										$icon = "<i class='fa fa-{$section['icon']}'></i>";
									} else {
										$icon = null;
									}
									if ( $section['title'] )
										echo "<h2>$icon {$section['title']}</h2>\n";
									if ( $section['callback'] )
										call_user_func( $section['callback'], $section );
									if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) ) {
										echo '</div>';
										continue;
									}
									echo '<table class="form-table">';
									do_settings_fields( $page, $section['id'] );
									echo '</table>';
									echo '
				          <p class="submit">
					          <input name="Submit" type="submit" class="button-primary" value="'.esc_attr('Save Changes','gb').'" />
				          </p>';
									echo '</div>';
								}
							}
						?>
					</div>
				</div>
			</div>
			</form>


			<div class="credits-container">
				<div class="row">
					<div class="col-xs-12 col-sm-6"><?= Minio::$version ?></div>
				</div>
			</div>
		</div><!-- wrap -->
		<?php
  }

	public function admin_notices(){
		if ( isset($_GET['page']) && $_GET['page'] !== 'minio_settings_page' ){
			return;
		}

		if ( isset( $_GET['settings-updated']) && $_GET['settings-updated'] === true ){
			add_settings_error( 'minio_settings_page', 'minio_settings_page', __( 'Successfully updated.' ) , 'updated');
		}

		settings_errors( 'minio_settings_page' );
	}

  protected function __clone() {

  }
}
