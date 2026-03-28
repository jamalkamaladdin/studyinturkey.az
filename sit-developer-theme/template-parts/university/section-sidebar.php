<?php
/**
 * Tək universitet — yan panel (keçidlər və veb sayt).
 *
 * @var int $university_id
 */

defined( 'ABSPATH' ) || exit;

$university_id = isset( $university_id ) ? absint( $university_id ) : 0;
if ( $university_id < 1 ) {
	return;
}

$website = (string) get_post_meta( $university_id, 'sit_website_url', true );
$archive = sit_theme_universities_archive_url();
?>
<div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-5 shadow-sm">
	<h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500"><?php esc_html_e( 'Bu səhifədə', 'studyinturkey' ); ?></h2>
	<nav class="mt-3 flex flex-col gap-1 text-sm" aria-label="<?php esc_attr_e( 'Səhifə bölmələri', 'studyinturkey' ); ?>">
		<a class="rounded-md px-2 py-1.5 text-slate-700 hover:bg-white hover:text-slate-900" href="#admission"><?php esc_html_e( 'Qəbul və təqdimat', 'studyinturkey' ); ?></a>
		<a class="rounded-md px-2 py-1.5 text-slate-700 hover:bg-white hover:text-slate-900" href="#programs"><?php esc_html_e( 'Proqramlar', 'studyinturkey' ); ?></a>
		<a class="rounded-md px-2 py-1.5 text-slate-700 hover:bg-white hover:text-slate-900" href="#dormitories"><?php esc_html_e( 'Yataqxanalar', 'studyinturkey' ); ?></a>
		<a class="rounded-md px-2 py-1.5 text-slate-700 hover:bg-white hover:text-slate-900" href="#campus"><?php esc_html_e( 'Kampuslar', 'studyinturkey' ); ?></a>
		<a class="rounded-md px-2 py-1.5 text-slate-700 hover:bg-white hover:text-slate-900" href="#scholarships"><?php esc_html_e( 'Təqaüdlər', 'studyinturkey' ); ?></a>
		<a class="rounded-md px-2 py-1.5 text-slate-700 hover:bg-white hover:text-slate-900" href="#faq"><?php esc_html_e( 'FAQ', 'studyinturkey' ); ?></a>
		<a class="rounded-md px-2 py-1.5 text-slate-700 hover:bg-white hover:text-slate-900" href="#reviews"><?php esc_html_e( 'Rəylər', 'studyinturkey' ); ?></a>
	</nav>
	<?php if ( '' !== $website ) : ?>
		<a href="<?php echo esc_url( $website ); ?>" class="mt-5 flex w-full items-center justify-center rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-700" rel="noopener noreferrer" target="_blank">
			<?php esc_html_e( 'Rəsmi sayt', 'studyinturkey' ); ?>
		</a>
	<?php endif; ?>
	<a href="<?php echo esc_url( $archive ); ?>" class="mt-2 block text-center text-sm font-medium text-brand-700 hover:text-brand-600">
		← <?php esc_html_e( 'Universitetlər siyahısı', 'studyinturkey' ); ?>
	</a>
</div>
