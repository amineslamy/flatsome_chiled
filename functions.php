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

	// ۱. رندر فیلد نوع پی‌نوشت به‌صورت یک سلکت HTML نیتیو (برای جلوگیری از مشکلات عدم ذخیره ACF در فرم کامنت)
	$acf_html = '<p class="comment-form-comment_type"><label style="display:block;font-weight:bold;margin-bottom:5px;" for="comment_type">نوع پی‌نوشت <span class="required">*</span></label><select name="comment_type" id="comment_type" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;"><option value="rewrite">بازنویسی خبر</option><option value="note">ملاحظه</option><option value="theory">نظریه</option></select></p>';

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

/**
 * ۸. اسکریپت کنترل شرطی وجوب فیلد نوع پی‌نوشت بر اساس پر بودن ادیتور
 */
add_action( 'wp_footer', 'flatsome_child_comment_validation_script' );
add_action( 'admin_footer', 'flatsome_child_comment_validation_script' );
function flatsome_child_comment_validation_script() {
	?>
	<script>
	(function(){
		var editorId = 'sahab_comment_editor';

		function getEditorText() {
			try {
				if (window.tinymce && tinymce.get(editorId) && !tinymce.get(editorId).isHidden()) {
					return tinymce.get(editorId).getContent({format:'text'}).trim();
				}
			} catch (e) {}
			var ta = document.getElementById(editorId);
			if (ta) return ta.value.trim();
			return '';
		}

		function setRequiredState() {
			var el = document.querySelector('#comment_type');
			if (!el) return;
			var txt = getEditorText();
			if (txt.length) {
				el.setAttribute('required', 'required');
			} else {
				el.removeAttribute('required');
			}
		}

		function attachEditorListeners() {
			if (window.tinymce && tinymce.get(editorId)) {
				try {
					var inst = tinyMCE.get(editorId);
					inst.on('keyup change NodeChange', function(){ setRequiredState(); });
				} catch (e) {}
			}
			var ta = document.getElementById(editorId);
			if (ta) {
				ta.addEventListener('input', setRequiredState);
			}
			
			var form = document.querySelector('form.comment-form, form#commentform, form[name="commentform"]');
			if (form) {
				form.addEventListener('submit', function(e){
					var txt = getEditorText();
					var el = document.querySelector('#comment_type');
					if (el && !txt.length) {
						el.removeAttribute('required');
					}
				});
			}
		}

		document.addEventListener('DOMContentLoaded', function(){
			setTimeout(function(){
				attachEditorListeners();
				setRequiredState();
			}, 400);
		});
	})();
	</script>
	<?php
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
    echo '<h4 style="margin:0 0 10px; font-size:14px; font-weight:bold;">پی‌نوشت‌های ثبت‌شده</h4>';
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
            echo '<li style="border-bottom:1px solid #eee; padding:10px 0;">';
            echo '<div style="font-size:13px; margin-bottom:4px;"><strong>' . esc_html( $author ) . '</strong> ' . $badge . '</div>';
            echo '<div style="font-size:12px; color:#555; margin-bottom:6px;">' . esc_html( $date ) . '</div>';
            echo '<div style="font-size:13px; color:#333;">' . wp_kses_post( wpautop( $comment->comment_content ) ) . '</div>';
            echo '</li>';
        }
        echo '</ul>';
    }
    echo '</div>';

    echo '<div class="sahab-backend-comment-entry" style="border:1px solid #ddd; padding:15px; border-radius:8px; background:#fbfbfb;">';
    echo '<p style="margin:0 0 10px;"><label for="sahab_backend_comment_type" style="display:block;font-weight:bold;margin-bottom:5px;">نوع پی‌نوشت جدید</label><select name="sahab_backend_comment_type" id="sahab_backend_comment_type" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;"><option value="rewrite">بازنویسی خبر</option><option value="note">ملاحظه</option><option value="theory">نظریه</option></select></p>';
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

    if ( isset( $_POST['sahab_backend_comment'] ) && isset( $_POST['sahab_backend_comment_type'] ) ) {
        $comment_content = wp_kses_post( wp_unslash( $_POST['sahab_backend_comment'] ) );
        $comment_type    = sanitize_text_field( wp_unslash( $_POST['sahab_backend_comment_type'] ) );
        $valid_types     = array( 'rewrite', 'note', 'theory' );

        if ( ! empty( $comment_content ) && in_array( $comment_type, $valid_types, true ) ) {
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

            if ( $comment_id && ! is_wp_error( $comment_id ) ) {
                update_comment_meta( $comment_id, 'comment_type', $comment_type );
                update_comment_meta( $comment_id, '_comment_type', 'field_6a50f060ebcfb' );
            }
        }
    }
}
