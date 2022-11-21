<?php

if( ! defined( 'ABSPATH' ) ) exit;

if( !class_exists('acf_field_icon_picker') ) :

class acf_field_icon_picker extends acf_field {

	public static $pluginSettings = [];

	public function initialize() {
		// vars
		$this->name     	= 'icon-picker';
		$this->label    	= __('Icon Picker', 'acf-icon-picker');
		$this->category 	= 'jquery';
		$this->l10n 		= ['error'	=> __('Error!', 'acf-icon-picker')];
		$this->settings 	= static::$pluginSettings;
		$this->path_suffix 	= apply_filters('acf_icon_path_suffix', 'assets/img/acf/');
		$this->path 		= apply_filters('acf_icon_path', $this->settings['path']).$this->path_suffix;
		$this->url 			= apply_filters('acf_icon_url', $this->settings['url']).$this->path_suffix;

		$this->defaults = [
			'default_value' => '',
			'initial_value' => '',
			'maxlength'     => '',
			'placeholder'   => '',
			'prepend'       => '',
			'append'        => '',
		];

		$priority_dir_lookup = get_stylesheet_directory().'/'.$this->path_suffix;

		if (file_exists($priority_dir_lookup)) {
			$this->path = $priority_dir_lookup;
			$this->url = get_stylesheet_directory_uri().'/'.$this->path_suffix;
		}
		
		$this->svgs = [];

		$files = array_diff(scandir($this->path), ['.', '..']);
		foreach ($files as $file) {
			if(pathinfo($file, PATHINFO_EXTENSION) == 'svg') {
				$exploded = explode('.', $file);
				$icon = [
					'name' => $exploded[0],
					'icon' => $file
				];
				array_push($this->svgs, $icon);
			}
		}
	}

	public function render_field_settings($field) {
		// default_value
		acf_render_field_setting(
			$field, [
				'label'        => __( 'Return Format', 'acf-icon-picker' ),
				'instructions' => '',
				'type'         => 'radio',
				'name'         => 'return_format',
				'layout'       => 'horizontal',
				'choices'      => [
					'name' => __( 'SVG Name', 'acf-icon-picker' ),
					'url'  => __( 'SVG URL', 'acf-icon-picker' ),
				],
			]
		);
	}

	public function format_value($value, $post_id, $field) {
		if (empty($value)) {
			return false;
		}

		if ( $field['return_format'] == 'url' ) {
			return "{$this->url}{$value}.svg";
		}

		return $value;
	}

	function render_field( $field ) {
		$input_icon = $field['value'] != "" ? $field['value'] : $field['initial_value'];
		$svg = $this->path . $input_icon . '.svg';
		?>
			<div class="acf-icon-picker">
				<div class="acf-icon-picker__img">
					<?php
						if ( file_exists( $svg ) ) {
							$svg = $this->url . $input_icon . '.svg';
							echo '<div class="acf-icon-picker__svg">';
						   	echo '<img src="'.$svg.'" alt=""/>';
						    echo '</div>';
						}else{
							echo '<div class="acf-icon-picker__svg">';
							echo '<span class="acf-icon-picker__svg--span">&plus;</span>';
						    echo '</div>';
						}
					?>
					<input type="hidden" readonly name="<?php echo esc_attr($field['name']) ?>" value="<?php echo esc_attr($input_icon) ?>"/>
				</div>
				<?php if ( $field['required' ] == false ) { ?>
					<span class="acf-icon-picker__remove">
						Remove
					</span>
				<?php } ?>
			</div>
		<?php
	}

	function input_admin_enqueue_scripts() {

		$url = $this->settings['url'];
		$version = $this->settings['version'];

		wp_register_script( 'acf-input-icon-picker', "{$url}assets/js/input.js", array('acf-input'), $version );
		wp_enqueue_script('acf-input-icon-picker');

		wp_localize_script( 'acf-input-icon-picker', 'iv', array(
			'path' => $this->url,
			'svgs' => $this->svgs,
			'no_icons_msg' => sprintf( esc_html__('To add icons, add your svg files in the /%s folder in your theme.', 'acf-icon-picker'), $this->path_suffix)
		) );

		wp_register_style( 'acf-input-icon-picker', "{$url}assets/css/input.css", array('acf-input'), $version );
		wp_enqueue_style('acf-input-icon-picker');
	}
}

acf_field_icon_picker::$pluginSettings = $this->settings;
acf_register_field_type('acf_field_icon_picker');

endif;
