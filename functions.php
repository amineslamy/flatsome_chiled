<?php
/**
 * پلتفرم گزارش‌دهی، دسته‌بندی و مدیریت داده‌های پژوهشی «سحاب»
 * تم چایلد اختصاصی بر روی قالب Flatsome
 */

/**
 * Use Relevanssi excerpts on the search results page only.
 * This stays scoped to is_search() so category/blog archives keep their normal excerpts.
 */
function flatsome_child_search_excerpt( $excerpt, $post = null ) {
	if ( ! is_search() ) {
		return $excerpt;
	}

	if ( function_exists( 'relevanssi_the_excerpt' ) ) {
		ob_start();
		relevanssi_the_excerpt();
		$relevanssi_excerpt = ob_get_clean();

		if ( ! empty( trim( $relevanssi_excerpt ) ) ) {
			return $relevanssi_excerpt;
		}
	}

	return $excerpt;
}

/**
 * ۹. نمایش نوع پی‌نوشت (ACF `comment_type`) به‌صورت یک نشان زیبا در کنار متن دیدگاه
 */
function flatsome_child_comment_type_badge( $comment_text, $comment = null ) {
	$comment_id = 0;
	if ( is_object( $comment ) && isset( $comment->comment_ID ) ) {
		$comment_id = $comment->comment_ID;
	} elseif ( is_numeric( $comment ) ) {
		$comment_id = (int) $comment;
	} elseif ( isset( $GLOBALS['comment'] ) && is_object( $GLOBALS['comment'] ) ) {
		$comment_id = $GLOBALS['comment']->comment_ID;
	}

	if ( ! $comment_id ) {
		return $comment_text;
	}

	$type = get_comment_meta( $comment_id, 'comment_type', true );
	if ( empty( $type ) ) {
		return $comment_text;
	}

	$map = array(
		'rewrite' => array( 'label' => 'بازنویسی خبر', 'color' => '#2196F3' ),
		'note'    => array( 'label' => 'ملاحظه',       'color' => '#FF9800' ),
		'theory'  => array( 'label' => 'نظریه',        'color' => '#9C27B0' ),
	);

	if ( ! isset( $map[ $type ] ) ) {
		return $comment_text;
	}

	$label = esc_html( $map[ $type ]['label'] );
	$color = esc_attr( $map[ $type ]['color'] );

	$badge = '<span class="sahab-badge" style="background: ' . $color . '; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 11px; margin-left: 5px; display: inline-block; font-weight: bold;">' . $label . '</span>';

	return $badge . ' ' . $comment_text;
}
add_filter( 'comment_text', 'flatsome_child_comment_type_badge', 10, 2 );

add_action('comment_post', 'flatsome_child_save_native_comment_type_meta', 10, 3);
function flatsome_child_save_native_comment_type_meta($comment_id, $comment_approved, $commentdata) {
	if ( isset($_POST['comment_type']) ) {
		$type = sanitize_text_field( wp_unslash( $_POST['comment_type'] ) );
		if ( in_array( $type, array( 'rewrite', 'note', 'theory' ), true ) ) {
			update_comment_meta( $comment_id, 'comment_type', $type );
			// Link to ACF field key so ACF UI recognizes the meta
			update_comment_meta( $comment_id, '_comment_type', 'field_6a50f060ebcfb' );
		}
	}
}

add_filter( 'the_excerpt', 'flatsome_child_search_excerpt', 20 );
add_filter( 'get_the_excerpt', 'flatsome_child_search_excerpt', 20 );


class Flatsome_Child_Theme_Init {

	/**
	 * The single instance of the class.
	 *
	 * @var Flatsome_Child_Theme_Init
	 */
	protected static $_instance = null;

	public static $version = '3.7.2';

	/**
	 * Flatsome_Child_Theme_Init constructor.
	 */
	public function __construct() {
		$this->load_dependencies();

		// Load text domain
		add_action( 'after_setup_theme', array($this, 'load_text_domain') );

		// Frontend assets
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

		// Add assets to admin
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts') );

		Flatsome_Child_Options::instance();
	}

	/**
	 * Load plugin text_domain.
	 *
	 * @since 1.0.0
	 */
	public function load_text_domain() {
		// load custom translation file for the parent theme
		load_theme_textdomain( 'flatsome', get_stylesheet_directory() . '/languages/parent' );
		// load translation file for the child theme
		load_child_theme_textdomain( 'flatsome-child', get_stylesheet_directory() . '/languages' );
	}

	public function enqueue_scripts() {
		// Css Files
		wp_enqueue_style('flatsome-child-theme', get_stylesheet_directory_uri().'/assets/public/css/flatsome-child-theme.css', array(), self::$version);

		$font = Flatsome_Child_Options::get_option('font', 'typography');
		if( isset($font) && !empty($font) ) {
			wp_enqueue_style('flatsome-child-'.$font.'font', get_stylesheet_directory_uri().'/assets/public/css/'.$font.'-font.css', array(), self::$version);
		}
	}

	public function admin_enqueue_scripts() {
		// Javascript Files
		wp_enqueue_script( 'flatsome-child', get_stylesheet_directory_uri() . '/assets/admin/js/flatsome-child.js', array( 'jquery' ), self::$version, true );
	}

	/**
	 * Load child theme dependency files
	 *
	 * @since 1.0.0
	 */
	private function load_dependencies() {
		require_once get_stylesheet_directory() . '/includes/override_functions.php';
		require_once get_stylesheet_directory() . '/includes/Flatsome_Child_Options.php';
	}

	/**
	 * Main Class Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
Flatsome_Child_Theme_Init::instance();


/**
 * ۱. مدیریت فیلد شماره اتوماسیون (ACF) - قفل کردن کادر در بک‌آند
 */
function flatsome_child_disable_automation_id_field( $field ) {
	$field['disabled'] = true;
	return $field;
}
add_filter( 'acf/load_field/name=automation_id', 'flatsome_child_disable_automation_id_field' );


/**
 * ۲. محاسبه خودکار و ترتیبی شماره اتوماسیون (شروع از 1234567)
 */
function flatsome_child_generate_automation_id( $post_id ) {
	if ( get_post_type( $post_id ) !== 'post' ) {
		return;
	}

	if ( get_post_meta( $post_id, 'automation_id', true ) ) {
		return;
	}

	global $wpdb;

	$meta_key = 'automation_id';
	$max_value = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT MAX(CAST(meta_value AS UNSIGNED)) FROM $wpdb->postmeta WHERE meta_key = %s",
			$meta_key
		)
	);

	$next_id = empty( $max_value ) ? 1234567 : (int) $max_value + 1;
	update_post_meta( $post_id, $meta_key, $next_id );
}
add_action( 'acf/save_post', 'flatsome_child_generate_automation_id', 5 );


/**
 * ۳. بارگذاری سراسری دارایی‌های دیت‌پیکر شمسی (Jalali Datepicker)
 */
function flatsome_child_jalali_datepicker_setup() {
	$base = get_stylesheet_directory_uri() . '/assets/admin';
	wp_enqueue_style( 'flatsome-child-jalali-datepicker-css', $base . '/css/jalali-datepicker.min.css', array(), null );
	wp_enqueue_script( 'flatsome-child-jalali-datepicker-js', $base . '/js/jalali-datepicker.min.js', array(), null, true );
}
add_action( 'admin_enqueue_scripts', 'flatsome_child_jalali_datepicker_setup' );
add_action( 'wp_enqueue_scripts', 'flatsome_child_jalali_datepicker_setup' );


/**
 * ۴. مقداردهی اولیه و هوشمند دیت‌پیکر بر روی فیلدهای هدف
 */
function flatsome_child_jalali_datepicker_footer_init() {
	?>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		try {
			var selectors = [
				'[data-name="event_date"] input',
				'input[name*="event_date"]',
				'input[id*="event_date"]'
			];

			var nodes = [];
			selectors.forEach(function(sel) {
				document.querySelectorAll(sel).forEach(function(n) {
					if (nodes.indexOf(n) === -1) nodes.push(n);
				});
			});

			if (nodes.length) {
				nodes.forEach(function(input) {
					try {
						input.setAttribute('data-jdp', 'true');
						input.setAttribute('autocomplete', 'off');
					} catch (e) {}
				});
			}

			if (typeof jalaliDatepicker !== 'undefined') {
				jalaliDatepicker.startWatch({
					minDate: 'attr',
					maxDate: 'attr',
					autoReadOnlyInput: true
				});
			}
		} catch (e) {
			if (window.console && console.error) console.error('JDP init error', e);
		}
	});
	</script>
	<?php
}
add_action( 'admin_footer', 'flatsome_child_jalali_datepicker_footer_init' );
add_action( 'wp_footer', 'flatsome_child_jalali_datepicker_footer_init' );


/**
 * ۵. تغییر واژه‌گزینی منوهای اصلی پیشخوان وردپرس
 */
function flatsome_child_rename_posts_menu() {
    global $menu, $submenu;

    foreach ( $menu as $key => $item ) {
        if ( isset( $item[0] ) && $item[0] === 'Posts' ) {
            $menu[ $key ][0] = 'مدیریت اخبار';
            break;
        }
    }

    if ( isset( $submenu['edit.php'] ) && is_array( $submenu['edit.php'] ) ) {
        foreach ( $submenu['edit.php'] as $index => $subitem ) {
            if ( isset( $subitem[0] ) && $subitem[0] === 'All Posts' ) {
                $submenu['edit.php'][ $index ][0] = 'همه اخبار';
            }
            if ( isset( $subitem[0] ) && $subitem[0] === 'Add New' ) {
                $submenu['edit.php'][ $index ][0] = 'ایجاد خبر';
            }
        }
    }
}
add_action( 'admin_menu', 'flatsome_child_rename_posts_menu' );


/**
 * ۶. تغییر برچسب‌های داخلی نوع نوشته نوشته‌ها (Post Object Labels)
 */
function flatsome_child_rename_post_labels() {
    global $wp_post_types;

    if ( isset( $wp_post_types['post'] ) ) {
        $labels = &$wp_post_types['post']->labels;
        $labels->name = 'اخبار';
        $labels->singular_name = 'اخبار';
        $labels->add_new = 'ایجاد خبر';
        $labels->add_new_item = 'ایجاد خبر جدید';
        $labels->edit_item = 'ویرایش خبر';
        $labels->new_item = 'خبر جدید';
        $labels->view_item = 'مشاهده خبر';
        $labels->search_items = 'جستجوی اخبار';
        $labels->not_found = 'خبری یافت نشد';
        $labels->not_found_in_trash = 'خبری در زباله‌دان یافت نشد';
    }
}
add_action( 'init', 'flatsome_child_rename_post_labels' );


/**
 * ۷. مهندسی مجدد و جراحی فرم پی‌نوشت‌های سحاب (حذف زواید، تغییر ترتیب، افزودن ادیتور پیشرفته)
 */
function flatsome_child_render_comment_type_dashboard( $post_id = 0 ) {
	if ( ! $post_id ) {
		$post = get_post();
		$post_id = $post && isset( $post->ID ) ? (int) $post->ID : 0;
	}

	if ( ! $post_id ) {
		return '';
	}

	$comments = get_comments( array(
		'post_id' => $post_id,
		'status'  => 'approve',
		'order'   => 'ASC',
	) );

	$counts = array(
		'note'    => 0,
		'theory'  => 0,
		'rewrite' => 0,
		'plain'   => 0,
	);

	foreach ( $comments as $comment ) {
		$type = get_comment_meta( $comment->comment_ID, 'comment_type', true );
		if ( $type === 'note' ) {
			$counts['note']++;
		} elseif ( $type === 'theory' ) {
			$counts['theory']++;
		} elseif ( $type === 'rewrite' ) {
			$counts['rewrite']++;
		} else {
			$counts['plain']++;
		}
	}

	$items = array(
		array( 'key' => 'note', 'label' => 'ملاحظه', 'color' => '#ff9800' ),
		array( 'key' => 'theory', 'label' => 'نظریه', 'color' => '#9c27b0' ),
		array( 'key' => 'rewrite', 'label' => 'بازنویسی', 'color' => '#2196f3' ),
		array( 'key' => 'plain', 'label' => 'متفرقه', 'color' => '#757575' ),
	);

	ob_start();
	?>
	<div style="margin:12px 0 16px; padding:12px 14px; border:1px solid #e0e0e0; border-radius:6px; background:#f9f9f9; display:flex; flex-wrap:wrap; align-items:center; gap:10px;">
		<span style="font-weight:600; color:#333;">📊 خلاصه وضعیت پی‌نوشت‌ها:</span>
		<?php foreach ( $items as $item ) : $value = isset( $counts[ $item['key'] ] ) ? (int) $counts[ $item['key'] ] : 0; ?>
		<span style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; color:#fff; background:<?php echo esc_attr( $item['color'] ); ?>; font-size:12px; font-weight:600; white-space:nowrap;">
			<span><?php echo esc_html( '[' . $value . '] ' . $item['label'] ); ?></span>
		</span>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}

add_filter( 'comments_number', 'flatsome_child_override_comments_number_title', 20, 5 );
function flatsome_child_override_comments_number_title( $output, $number = 0, $zero = '', $one = '', $more = '' ) {
	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return '';
	}
	return flatsome_child_render_comment_type_dashboard( $post_id );
}

add_action( 'comment_form_before', 'flatsome_child_inject_frontend_dashboard' );
function flatsome_child_inject_frontend_dashboard() {
	if ( is_admin() ) {
		return;
	}
	$post_id = get_the_ID();
	if ( ! $post_id || ! comments_open( $post_id ) ) {
		return;
	}
	echo flatsome_child_render_comment_type_dashboard( $post_id );
}

add_filter( 'comment_form_defaults', 'flatsome_child_force_comment_form_strings' );
function flatsome_child_force_comment_form_strings( $defaults ) {
	$defaults['logged_in_as'] = '';
	$defaults['title_reply']  = 'ثبت پی‌نوشت جدید';
	$defaults['label_submit'] = 'ثبت و ارسال';
	return $defaults;
}

add_filter( 'comment_form_fields', 'flatsome_child_reorder_and_enrich_comment_fields' );
function flatsome_child_reorder_and_enrich_comment_fields( $fields ) {
	// لود دارایی‌های ادیتور پیشرفته در فرانت‌آند
	if ( ! is_admin() ) {
		wp_enqueue_editor();
	}

	// ۱. رندر فیلد نوع پی‌نوشت به‌صورت رادیوهای این‌لاین و کم‌حجم
	$acf_html = '<p class="comment-form-comment_type"><label style="display:block;font-weight:bold;margin-bottom:5px;">نوع پی‌نوشت</label><span style="display:flex; gap:15px; align-items:center; flex-wrap:wrap;"><label style="font-weight:normal;"><input type="radio" name="comment_type" value="" checked style="margin-left:5px;">هیچ‌کدام</label><label style="font-weight:normal;"><input type="radio" name="comment_type" value="rewrite" style="margin-left:5px;">بازنویسی خبر</label><label style="font-weight:normal;"><input type="radio" name="comment_type" value="note" style="margin-left:5px;">ملاحظه</label><label style="font-weight:normal;"><input type="radio" name="comment_type" value="theory" style="margin-left:5px;">نظریه</label></span></p>';

	// ۲. ساخت کادر متنی مجهز به ویرایشگر حرفه‌ای و حداقلی وردپرس (TinyMCE)
	ob_start();
	$editor_settings = array(
		'textarea_name' => 'comment',
		'media_buttons' => false,
		'teeny'         => true,
		'textarea_rows' => 6,
		'tinymce'       => array(
			'toolbar1' => 'bold italic underline bullist',
			'toolbar2' => '',
		),
		'quicktags'     => false,
	);
	wp_editor( '', 'sahab_comment_editor', $editor_settings );
	$editor_html = ob_get_clean();

	// ایجاد آرایه جدید با چیدمان مهندسی‌شده سحاب (حذف فیلد پیش‌فرض و چسباندن ساختار جدید)
	$custom_fields = array();
	$custom_fields['comment_field'] = '<div class="sahab-comment-fields-wrapper">' . $acf_html . '<p class="comment-form-comment"><label for="sahab_comment_editor">متن پی‌نوشت <span class="required">*</span></label>' . $editor_html . '</p></div>';

	return $custom_fields;
}

add_action( 'add_meta_boxes', 'flatsome_child_remove_core_comments_meta_box', 99 );
function flatsome_child_remove_core_comments_meta_box() {
    remove_meta_box( 'commentsdiv', 'post', 'normal' );
}

add_action( 'add_meta_boxes', 'flatsome_child_add_sahab_custom_comments_meta_box' );
function flatsome_child_add_sahab_custom_comments_meta_box() {
    add_meta_box(
        'sahab_custom_comments_meta_box',
        'مدیریت و ثبت پی‌نوشت‌های سحاب (ملاحظات، نظریات و بازنویسی)',
        'flatsome_child_render_sahab_custom_comments_meta_box',
        'post',
        'normal',
        'high'
    );
}

function flatsome_child_render_sahab_custom_comments_meta_box( $post ) {
    wp_nonce_field( 'sahab_custom_comments_meta_box', 'sahab_custom_comments_meta_box_nonce' );

    $comments = get_comments( array(
        'post_id' => $post->ID,
        'status'  => 'approve',
        'order'   => 'DESC',
    ) );

    echo '<div class="sahab-backend-comments-box" style="margin-bottom:20px;">';

    echo flatsome_child_render_comment_type_dashboard( $post->ID );

    echo '<div id="sahab-backend-comment-form" class="sahab-backend-comment-entry" style="border:1px solid #ddd; padding:15px; border-radius:8px; background:#fbfbfb; margin-bottom:20px;">';
    echo '<input type="hidden" id="sahab_backend_comment_post_id" value="' . esc_attr( $post->ID ) . '">';
    echo '<input type="hidden" id="sahab_active_comment_id" value="">';
    echo '<p style="margin:0 0 10px;"><label style="display:block;font-weight:bold;margin-bottom:5px;">نوع پی‌نوشت جدید</label><span style="display:flex; gap:15px; align-items:center; flex-wrap:wrap;"><label style="font-weight:normal;"><input type="radio" name="sahab_backend_comment_type" value="" checked style="margin-left:5px;">هیچ‌کدام</label><label style="font-weight:normal;"><input type="radio" name="sahab_backend_comment_type" value="rewrite" style="margin-left:5px;">بازنویسی خبر</label><label style="font-weight:normal;"><input type="radio" name="sahab_backend_comment_type" value="note" style="margin-left:5px;">ملاحظه</label><label style="font-weight:normal;"><input type="radio" name="sahab_backend_comment_type" value="theory" style="margin-left:5px;">نظریه</label></span></p>';
    ob_start();
    $editor_settings = array(
        'textarea_name' => 'sahab_backend_comment',
        'media_buttons' => false,
        'teeny'         => true,
        'textarea_rows' => 6,
        'tinymce'       => array(
            'toolbar1' => 'bold italic underline bullist',
            'toolbar2' => '',
        ),
        'quicktags'     => false,
    );
    wp_editor( '', 'sahab_backend_comment_editor', $editor_settings );
    $backend_editor_html = ob_get_clean();
    echo '<p style="margin:20px 0 0; font-weight:bold;">متن پی‌نوشت جدید</p>' . $backend_editor_html;
    echo '<button type="button" id="sahab-submit-backend-comment" class="button button-primary" style="margin-top:10px;">ثبت و ارسال پی‌نوشت</button>';
    echo '<div id="sahab-backend-comment-feedback" style="margin-top:10px; min-height:20px;"></div>';
    echo '</div>';

    echo '<h4 style="margin:0 0 10px; font-size:14px; font-weight:bold;">پی‌نوشت‌های ثبت‌شده</h4>';
    echo '<div id="sahab-backend-comments-list">';
    if ( empty( $comments ) ) {
        echo '<p style="color:#666;">هنوز پی‌نوشتی ثبت نشده است.</p>';
    } else {
        echo '<ul style="list-style:none; margin:0; padding:0;">';
        foreach ( $comments as $comment ) {
            $type = get_comment_meta( $comment->comment_ID, 'comment_type', true );
            $map = array(
                'rewrite' => array( 'label' => 'بازنویسی خبر', 'color' => '#2196F3' ),
                'note'    => array( 'label' => 'ملاحظه',       'color' => '#FF9800' ),
                'theory'  => array( 'label' => 'نظریه',        'color' => '#9C27B0' ),
            );
            $badge = '';
            if ( isset( $map[ $type ] ) ) {
                $badge = '<span style="background:' . esc_attr( $map[ $type ]['color'] ) . '; color:#fff; padding:2px 8px; border-radius:3px; font-size:11px; display:inline-block; font-weight:bold; margin-left:8px;">' . esc_html( $map[ $type ]['label'] ) . '</span>';
            }
            $author = get_comment_author( $comment->comment_ID );
            $date   = get_comment_date( 'Y/m/d H:i', $comment->comment_ID );
            $raw_content = wp_strip_all_tags( $comment->comment_content );
            echo '<li class="sahab-comment-item" data-comment-id="' . esc_attr( $comment->comment_ID ) . '" data-comment-content="' . esc_attr( trim( $raw_content ) ) . '" data-comment-type="' . esc_attr( $type ) . '" style="border-bottom:1px solid #eee; padding:10px 0;">';
            echo '<div style="font-size:13px; margin-bottom:4px;"><strong>' . esc_html( $author ) . '</strong> ' . $badge . '<span class="sahab-action-links" style="margin-right:15px;"><a href="#" class="sahab-edit-comment" data-comment-id="' . esc_attr( $comment->comment_ID ) . '" style="color:#007cba; text-decoration:none; margin-left:10px;">✏️ ویرایش</a><a href="#" class="sahab-delete-comment" data-comment-id="' . esc_attr( $comment->comment_ID ) . '" style="color:#d94f4f; text-decoration:none;">❌ حذف</a></span></div>';
            echo '<div style="font-size:12px; color:#555; margin-bottom:6px;">' . esc_html( $date ) . '</div>';
            echo '<div style="font-size:13px; color:#333;">' . wp_kses_post( wpautop( $comment->comment_content ) ) . '</div>';
            echo '</li>';
        }
        echo '</ul>';
    }
    echo '</div>';
    echo '</div>';
}

add_action( 'save_post', 'flatsome_child_save_backend_comment_from_meta_box', 20, 3 );
function flatsome_child_save_backend_comment_from_meta_box( $post_id, $post, $update ) {
    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }
    if ( ! isset( $_POST['sahab_custom_comments_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['sahab_custom_comments_meta_box_nonce'], 'sahab_custom_comments_meta_box' ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['sahab_backend_comment'] ) ) {
        $comment_content = wp_kses_post( wp_unslash( $_POST['sahab_backend_comment'] ) );
        $comment_type    = isset( $_POST['sahab_backend_comment_type'] ) ? sanitize_text_field( wp_unslash( $_POST['sahab_backend_comment_type'] ) ) : '';
        $valid_types     = array( 'rewrite', 'note', 'theory' );

        if ( ! empty( $comment_content ) ) {
            $comment_id = wp_insert_comment( array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => wp_get_current_user()->display_name,
                'comment_author_email' => wp_get_current_user()->user_email,
                'comment_author_url'   => '',
                'comment_content'      => $comment_content,
                'comment_type'         => '',
                'comment_parent'       => 0,
                'user_id'              => get_current_user_id(),
                'comment_approved'     => 1,
            ) );

            if ( $comment_id && ! is_wp_error( $comment_id ) && ! empty( $comment_type ) && in_array( $comment_type, $valid_types, true ) ) {
                update_comment_meta( $comment_id, 'comment_type', $comment_type );
                update_comment_meta( $comment_id, '_comment_type', 'field_6a50f060ebcfb' );
            }
        }
    }
}

add_action( 'wp_ajax_sahab_submit_backend_comment', 'flatsome_child_ajax_submit_backend_comment' );
function flatsome_child_ajax_submit_backend_comment() {
    if ( ! isset( $_POST['post_id'], $_POST['comment_type'], $_POST['comment_content'], $_POST['sahab_custom_comments_meta_box_nonce'] ) ) {
        wp_send_json_error( 'missing_fields' );
    }

    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sahab_custom_comments_meta_box_nonce'] ) ), 'sahab_custom_comments_meta_box' ) ) {
        wp_send_json_error( 'invalid_nonce' );
    }

    $post_id        = absint( $_POST['post_id'] );
    $comment_type   = isset( $_POST['comment_type'] ) ? sanitize_text_field( wp_unslash( $_POST['comment_type'] ) ) : '';
    $comment_content = wp_kses_post( wp_unslash( $_POST['comment_content'] ) );
    $valid_types    = array( 'rewrite', 'note', 'theory' );

    if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) || empty( $comment_content ) ) {
        wp_send_json_error( 'invalid_data' );
    }

    $current_user = wp_get_current_user();
    $comment_id = wp_insert_comment( array(
        'comment_post_ID'      => $post_id,
        'comment_author'       => $current_user->display_name,
        'comment_author_email' => $current_user->user_email,
        'comment_author_url'   => '',
        'comment_content'      => $comment_content,
        'comment_type'         => '',
        'comment_parent'       => 0,
        'user_id'              => $current_user->ID,
        'comment_approved'     => 1,
    ) );

    if ( ! $comment_id || is_wp_error( $comment_id ) ) {
        wp_send_json_error( 'insert_failed' );
    }

    if ( ! empty( $comment_type ) && in_array( $comment_type, $valid_types, true ) ) {
        update_comment_meta( $comment_id, 'comment_type', $comment_type );
        update_comment_meta( $comment_id, '_comment_type', 'field_6a50f060ebcfb' );
    }

    $comment = get_comment( $comment_id );
    if ( ! $comment ) {
        wp_send_json_error( 'comment_not_found' );
    }

    $map = array(
        'rewrite' => array( 'label' => 'بازنویسی خبر', 'color' => '#2196F3' ),
        'note'    => array( 'label' => 'ملاحظه',       'color' => '#FF9800' ),
        'theory'  => array( 'label' => 'نظریه',        'color' => '#9C27B0' ),
    );
    $badge = '';
    if ( isset( $map[ $comment_type ] ) ) {
        $badge = '<span style="background:' . esc_attr( $map[ $comment_type ]['color'] ) . '; color:#fff; padding:2px 8px; border-radius:3px; font-size:11px; display:inline-block; font-weight:bold; margin-left:8px;">' . esc_html( $map[ $comment_type ]['label'] ) . '</span>';
    }

    $html  = '<li style="border-bottom:1px solid #eee; padding:10px 0;">';
    $html .= '<div style="font-size:13px; margin-bottom:4px;"><strong>' . esc_html( $current_user->display_name ) . '</strong> ' . $badge . '</div>';
    $html .= '<div style="font-size:12px; color:#555; margin-bottom:6px;">' . esc_html( get_comment_date( 'Y/m/d H:i', $comment ) ) . '</div>';
    $html .= '<div style="font-size:13px; color:#333;">' . wp_kses_post( wpautop( $comment->comment_content ) ) . '</div>';
    $html .= '</li>';

    wp_send_json_success( $html );
}

add_action( 'wp_ajax_sahab_delete_backend_comment', 'flatsome_child_ajax_delete_backend_comment' );
function flatsome_child_ajax_delete_backend_comment() {
    if ( ! isset( $_POST['comment_id'] ) ) {
        wp_send_json_error( 'missing_comment_id' );
    }

    $comment_id = absint( $_POST['comment_id'] );
    $comment    = get_comment( $comment_id );
    if ( ! $comment || ! current_user_can( 'edit_post', $comment->comment_post_ID ) ) {
        wp_send_json_error( 'forbidden' );
    }

    $deleted = wp_delete_comment( $comment_id, true );
    if ( $deleted ) {
        wp_send_json_success( array( 'deleted_id' => $comment_id ) );
    }

    wp_send_json_error( 'delete_failed' );
}

add_action( 'wp_ajax_sahab_edit_backend_comment', 'flatsome_child_ajax_edit_backend_comment' );
function flatsome_child_ajax_edit_backend_comment() {
    if ( ! isset( $_POST['comment_id'], $_POST['comment_type'], $_POST['comment_content'] ) ) {
        wp_send_json_error( 'missing_fields' );
    }

    $comment_id      = absint( $_POST['comment_id'] );
    $comment_type    = sanitize_text_field( wp_unslash( $_POST['comment_type'] ) );
    $comment_content = wp_kses_post( wp_unslash( $_POST['comment_content'] ) );
    $valid_types     = array( 'rewrite', 'note', 'theory' );
    $comment         = get_comment( $comment_id );

    if ( ! $comment || empty( $comment_content ) || ! current_user_can( 'edit_post', $comment->comment_post_ID ) ) {
        wp_send_json_error( 'invalid_data' );
    }

    $updated = wp_update_comment( array(
        'comment_ID'      => $comment_id,
        'comment_content' => $comment_content,
        'comment_approved' => 1,
    ) );

    if ( ! $updated || is_wp_error( $updated ) ) {
        wp_send_json_error( 'update_failed' );
    }

    if ( ! empty( $comment_type ) && in_array( $comment_type, $valid_types, true ) ) {
        update_comment_meta( $comment_id, 'comment_type', $comment_type );
        update_comment_meta( $comment_id, '_comment_type', 'field_6a50f060ebcfb' );
    } else {
        delete_comment_meta( $comment_id, 'comment_type' );
        delete_comment_meta( $comment_id, '_comment_type' );
    }

    wp_send_json_success( array( 'updated_id' => $comment_id ) );
}

add_action( 'admin_footer', 'flatsome_child_backend_comment_ajax_script' );
function flatsome_child_backend_comment_ajax_script() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        var activeCommentId = '';

        function getEditorContent() {
            if (window.tinymce && tinymce.get('sahab_backend_comment_editor')) {
                return tinymce.get('sahab_backend_comment_editor').getContent().trim();
            }
            return $('#sahab_backend_comment_editor').val().trim();
        }

        function clearEditor() {
            if (window.tinymce && tinymce.get('sahab_backend_comment_editor')) {
                tinymce.get('sahab_backend_comment_editor').setContent('');
            } else {
                $('#sahab_backend_comment_editor').val('');
            }
        }

        function resetComposer() {
            clearEditor();
            $('input[name="sahab_backend_comment_type"]').prop('checked', false);
            $('input[name="sahab_backend_comment_type"][value=""]').prop('checked', true);
            activeCommentId = '';
            $('#sahab_active_comment_id').val('');
            $('#sahab-submit-backend-comment').text('ثبت و ارسال پی‌نوشت');
        }

        $('#sahab-submit-backend-comment').on('click', function() {
            var button = $(this);
            var postId = $('#sahab_backend_comment_post_id').val();
            var commentType = $('input[name="sahab_backend_comment_type"]:checked').val() || '';
            var content = getEditorContent();

            if ( ! content ) {
                alert('لطفاً متن پی‌نوشت را وارد کنید.');
                return;
            }

            button.prop('disabled', true).text('در حال ارسال...');
            $('#sahab-backend-comment-feedback').html('');

            var payload = {
                action: 'sahab_submit_backend_comment',
                post_id: postId,
                comment_type: commentType,
                comment_content: content,
                sahab_custom_comments_meta_box_nonce: $('#sahab_custom_comments_meta_box_nonce').val()
            };

            if (activeCommentId) {
                payload.action = 'sahab_edit_backend_comment';
                payload.comment_id = activeCommentId;
            }

            $.post(ajaxurl, payload, function(response) {
                if ( response.success ) {
                    if (activeCommentId) {
                        var item = $('.sahab-comment-item[data-comment-id="' + activeCommentId + '"]');
                        if (item.length) {
                            item.attr('data-comment-content', content);
                            item.attr('data-comment-type', commentType || '');
                            item.find('div:last').html(content);
                        }
                        $('#sahab-backend-comment-feedback').html('<div style="color:green;">پی‌نوشت با موفقیت به‌روزرسانی شد.</div>');
                    } else {
                        var html = response.data;
                        if ( $('#sahab-backend-comments-list ul').length ) {
                            $('#sahab-backend-comments-list ul').prepend(html);
                        } else {
                            $('#sahab-backend-comments-list').html('<ul style="list-style:none; margin:0; padding:0;">' + html + '</ul>');
                        }
                        $('#sahab-backend-comment-feedback').html('<div style="color:green;">پی‌نوشت با موفقیت ثبت شد.</div>');
                    }
                    resetComposer();
                } else {
                    $('#sahab-backend-comment-feedback').html('<div style="color:red;">خطا در ارسال پی‌نوشت. لطفاً دوباره تلاش کنید.</div>');
                }
            }).fail(function() {
                $('#sahab-backend-comment-feedback').html('<div style="color:red;">خطای شبکه رخ داد.</div>');
            }).always(function() {
                button.prop('disabled', false).text(activeCommentId ? 'ذخیره تغییرات پی‌نوشت' : 'ثبت و ارسال پی‌نوشت');
            });
        });

        $(document).on('click', '.sahab-delete-comment', function(e) {
            e.preventDefault();
            var id = $(this).data('comment-id');
            if ( ! confirm('آیا از حذف این پی‌نوشت مطمئن هستید؟') ) {
                return;
            }
            $.post(ajaxurl, {
                action: 'sahab_delete_backend_comment',
                comment_id: id
            }, function(response) {
                if ( response.success ) {
                    $('.sahab-comment-item[data-comment-id="' + id + '"]').remove();
                }
            });
        });

        $(document).on('click', '.sahab-edit-comment', function(e) {
            e.preventDefault();
            var id = $(this).data('comment-id');
            var item = $('.sahab-comment-item[data-comment-id="' + id + '"]');
            if ( ! item.length ) {
                return;
            }
            var content = item.attr('data-comment-content') || '';
            var type = item.attr('data-comment-type') || '';
            activeCommentId = id;
            $('#sahab_active_comment_id').val(id);
            if (window.tinymce && tinymce.get('sahab_backend_comment_editor')) {
                tinymce.get('sahab_backend_comment_editor').setContent(content);
            } else {
                $('#sahab_backend_comment_editor').val(content);
            }
            $('input[name="sahab_backend_comment_type"]').prop('checked', false);
            if (type) {
                $('input[name="sahab_backend_comment_type"][value="' + type + '"]').prop('checked', true);
            } else {
                $('input[name="sahab_backend_comment_type"][value=""]').prop('checked', true);
            }
            $('#sahab-submit-backend-comment').text('ذخیره تغییرات پی‌نوشت');
            $('#sahab-backend-comment-feedback').html('<div style="color:#007cba;">در حال ویرایش پی‌نوشت…</div>');
        });
    });
    </script>
    <?php
}

