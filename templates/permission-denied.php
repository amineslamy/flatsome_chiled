<?php
/**
 * پلتفرم سحاب - قالب اختصاصی و مینی‌مال صفحه عدم دسترسی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>خطای دسترسی | سامانه سحاب</title>
	<link rel="stylesheet" href="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/public/css/sahab-errors.css' ); ?>">
</head>
<body class="sahab-error-body">

	<div class="sahab-error-card" dir="rtl">
		<div class="sahab-error-icon">⚠️</div>
		<h1 class="sahab-error-title">عدم دسترسی کافی</h1>
		<p class="sahab-error-message">با عرض پوزش، حساب کاربری شما اجازهٔ ویرایش یا دسترسی به این خبر را ندارد.</p>
		
		<div class="sahab-error-actions">
			<a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="sahab-error-btn btn-primary">بازگشت به میز کار مرکزی</a>
			<a href="javascript:history.back()" class="sahab-error-btn btn-secondary">بازگشت به صفحه قبلی</a>
		</div>
	</div>

</body>
</html>