<?php
/**
 * Çörək qırıntısı.
 *
 * @var array<int, array{label: string, url?: string}> $items get_template_part $args.
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $items ) || ! is_array( $items ) || empty( $items ) ) {
	return;
}
?>
<nav class="text-sm text-slate-500" aria-label="<?php esc_attr_e( 'Çörək qırıntısı', 'studyinturkey' ); ?>">
	<div class="flex flex-wrap items-center gap-x-1.5 gap-y-1">
		<?php
		$last = count( $items ) - 1;
		foreach ( $items as $i => $item ) {
			$label = isset( $item['label'] ) ? (string) $item['label'] : '';
			$url   = isset( $item['url'] ) ? (string) $item['url'] : '';
			if ( '' === $label ) {
				continue;
			}
			if ( $i > 0 ) {
				echo '<span class="text-slate-300" aria-hidden="true">/</span>';
			}
			echo '<span class="min-w-0">';
			if ( $url !== '' && $i < $last ) {
				printf(
					'<a href="%s" class="truncate hover:text-slate-800">%s</a>',
					esc_url( $url ),
					esc_html( $label )
				);
			} else {
				printf( '<span class="truncate font-medium text-slate-700" aria-current="page">%s</span>', esc_html( $label ) );
			}
			echo '</span>';
		}
		?>
	</div>
</nav>
