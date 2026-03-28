<?php
/**
 * Slug-u *-fbu ilə bitən bütün proqramlara Fenerbahçe universitet ID-sini yazır.
 * Admin-də proqram saxlananda boş universitet sətri meta siləndə də işlədin.
 *
 *   php8.3 wp-content/plugins/sit-developer/bin/fix-fbu-program-university-meta.php
 *
 * @package StudyInTurkey
 */

if ( php_sapi_name() !== 'cli' ) {
	exit( 'CLI only.' );
}

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	fwrite( STDERR, "wp-load.php tapılmadı.\n" );
	exit( 1 );
}

require $wp_load;

$univ = get_posts(
	[
		'post_type'              => 'university',
		'post_status'            => [ 'publish', 'draft', 'private', 'pending' ],
		'name'                   => 'fenerbahce-universitesi',
		'posts_per_page'         => 1,
		'fields'                 => 'ids',
		'update_post_meta_cache' => false,
	]
);
if ( ! $univ ) {
	fwrite( STDERR, "fenerbahce-universitesi tapılmadı.\n" );
	exit( 1 );
}
$univ_id = (int) $univ[0];

global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$pids = $wpdb->get_col(
	$wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND post_name LIKE %s",
		'program',
		'publish',
		'%' . $wpdb->esc_like( '-fbu' )
	)
);

$n = 0;
foreach ( $pids as $pid ) {
	$pid = (int) $pid;
	$cur = (int) get_post_meta( $pid, 'sit_university_id', true );
	if ( $cur !== $univ_id ) {
		update_post_meta( $pid, 'sit_university_id', $univ_id );
		++$n;
	}
}

echo 'Fenerbahçe universitet ID: ' . $univ_id . "\n";
echo 'Yoxlanılan proqram: ' . count( $pids ) . "\n";
echo 'Yenilənən meta: ' . $n . "\n";

if ( class_exists( 'SIT_REST_API' ) ) {
	SIT_REST_API::bump_cache();
	echo "REST keş versiyası artırıldı.\n";
}
