<?php
/**
 * Created by PhpStorm.
 * User: iran flatsome
 * Date: 18/11/23
 * Time: 8:42 AM
 */

class Flatsome_Child_Options {
	/**
	 * The single instance of the class.
	 *
	 * @var Flatsome_Child_Theme_Init
	 */
	protected static $_instance = null;

	public $fonts;

	/**
	 * Flatsome_Child_Options constructor.
	 */
	public function __construct() {

		add_action('init', array($this, 'set_values'));

		// Add settings menu page
		add_action('admin_menu', array($this, 'admin_menu'), 99);
		add_action( 'admin_init', array($this, 'settings_init'));
	}

	public function set_values() {
		$this->fonts = array(
			'aria' => __('آریا (جدید)', 'flatsome-child'),
			'iransansx' => __('ایران سنس ایکس (جدید)', 'flatsome-child'),
			'bakh' => __('یکان باخ (جدید)', 'flatsome-child'),
			'iranyekanx' => __('ایران یکان ایکس', 'flatsome-child'),
			'iransans' => __('ایران سنس', 'flatsome-child'),
			'iransans-farsi-numbers' => __('ایران سنس با اعداد فارسی', 'flatsome-child'),
			'iransans-dn' => __('ایران سنس دست نویس', 'flatsome-child'),
			'iranyekan' => __('ایران یکان', 'flatsome-child'),
			'iranyekan-farsi-numbers' => __('ایران یکان با اعداد فارسی', 'flatsome-child'),
			'iransharp' => __('ایران شارپ', 'flatsome-child'),
			'vazir' => __('وزیر', 'flatsome-child'),
			'sahel' => __('ساحل', 'flatsome-child'),
			'shabnam' => __('شبنم', 'flatsome-child'),
			'samim' => __('صمیم', 'flatsome-child'),
			'gandom' => __('گندم', 'flatsome-child'),
			'tanha' => __('تنها', 'flatsome-child'),
			'parastoo' => __('پرستو', 'flatsome-child'),
			'dirooz' => __('دیروز', 'flatsome-child'),
			'anjoman' => __('انجمن', 'flatsome-child'),
			'dana' => __('دانا', 'flatsome-child'),
			'dana-fa' => __('دانا (با اعداد فارسی)', 'flatsome-child'),
		);
	}

	public function admin_menu() {
		add_submenu_page(
			'flatsome-panel',
			__('Persian settings', 'flatsome-child'),
			__('Persian settings', 'flatsome-child'),
			'manage_options',
			'flatsome-persian-settings',
			array($this, 'admin_menu_content')
		);
	}

	public function admin_menu_content() {
		?>
		<div class="wrap">
			<h1><?php _e('Persian settings', 'flatsome-child') ?></h1>
			<?php $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general'; ?>
			<h2 class="nav-tab-wrapper" style="margin-bottom: 10px">
				<a href="<?php echo admin_url('admin.php?page=flatsome-persian-settings&tab=general') ?>" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e('General', 'flatsome-child') ?></a>
			</h2>

			<form method="post" action="options.php">
				<?php
				settings_errors();
				if( $active_tab == 'general' ) {
					settings_fields( 'flatsome-child-group' );
					do_settings_sections( 'flatsome-child-general-page' );
				} elseif( $active_tab == 'notifications' ) {
					settings_fields( 'flatsome-child-group' );
					do_settings_sections( 'zhk_notifications_page' );
				}
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function settings_init(  ) {
		register_setting(
			'flatsome-child-group',
			'flatsome-child-options'
		);

		// Settings Sections
		add_settings_section(
			'typography',
			__('Typography', 'flatsome-child'),
			NULL,
			'flatsome-child-general-page'
		);

		// Settings fields
		add_settings_field(
			'font',
			__('Font', 'flatsome-child'),
			array($this, 'font_callback'),
			'flatsome-child-general-page',
			'typography'
		);
	}

	public function font_callback() {
		?>
		<select name="flatsome-child-options[typography][font]" id="font">
            <option value=""><?php _e('Theme default', 'flatsome-child'); ?></option>
			<?php foreach ($this->fonts as $font_key => $font_title): ?>
				<option value="<?php echo $font_key ?>" <?php selected( self::get_option('font', 'typography'), $font_key ) ?>><?php echo $font_title ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public static function get_option($option_name = false, $option_group = false)
	{
		$options = get_option('flatsome-child-options');
		$sorted_options = array();


		$sorted_options['typography']['font'] = (isset($options['typography']['font']) && !empty($options['typography']['font'])) ? $options['typography']['font'] : '';

		if( !empty($option_name) && !empty($option_group) ) {
			return $sorted_options[$option_group][$option_name];
		} else {
			return $options;
		}
	}


	/**
	 * Main Class Instance.
	 *
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @static
	 * @return Flatsome_Child_Theme_Init - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}