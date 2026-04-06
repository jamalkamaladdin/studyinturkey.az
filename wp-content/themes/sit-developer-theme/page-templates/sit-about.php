<?php
/**
 * Template Name: SIT — Haqqımızda
 * Template Post Type: page
 */
defined( 'ABSPATH' ) || exit;
get_header();
$img = 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080';
?>
<main id="main-content" class="flex-1">
	<!-- Hero -->
	<div class="relative overflow-hidden bg-[#11676a]">
		<div class="absolute inset-0 opacity-20"><img src="<?php echo esc_url( $img ); ?>" alt="" class="h-full w-full object-cover" /></div>
		<div class="absolute inset-0 bg-gradient-to-b from-[#0a1a1b]/60 to-[#11676a]"></div>
		<div class="absolute top-0 right-0 h-[500px] w-[500px] rounded-full bg-brand-600/15 blur-[120px]"></div>
		<div class="relative mx-auto max-w-7xl px-4 pt-20 pb-28 sm:px-6">
			<div class="max-w-2xl">
				<div class="mb-6 flex items-center gap-3">
					<div class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5">
						<svg class="h-[18px] w-[18px] text-[#ff3131]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.27 2 8.5 2 5.41 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.08C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.41 22 8.5c0 3.77-3.4 6.86-8.55 11.53L12 21.35z"/></svg>
					</div>
					<span class="text-[13px] font-semibold uppercase tracking-[0.2em] text-white/40"><?php esc_html_e( 'Our Story', 'studyinturkey' ); ?></span>
				</div>
				<h1 class="mb-4 text-[42px] tracking-[-0.03em] text-white md:text-[56px]" style="line-height:1.1"><?php the_title(); ?></h1>
				<p class="max-w-lg text-[17px] leading-relaxed text-white/40"><?php esc_html_e( 'Your trusted bridge to world-class education in Turkey. We empower students to achieve their academic and professional dreams.', 'studyinturkey' ); ?></p>
			</div>
		</div>
	</div>

	<!-- Content -->
	<div class="bg-[#f3f6f6]">
		<div class="mx-auto max-w-7xl px-4 py-20 sm:px-6">
			<!-- Who We Are -->
			<div class="mb-24 grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
				<div>
					<span class="mb-4 block text-[12px] font-bold uppercase tracking-[0.15em] text-brand-600"><?php esc_html_e( 'Who We Are', 'studyinturkey' ); ?></span>
					<h2 class="mb-6 text-[32px] text-[#0a1a1b] tracking-[-0.02em] md:text-[40px]" style="line-height:1.15"><?php esc_html_e( 'Bridging students to their dream universities', 'studyinturkey' ); ?></h2>
					<p class="mb-5 text-[15px] leading-relaxed text-gray-500"><?php esc_html_e( 'StudyInTurkey is a premier educational consulting agency dedicated to connecting international students with top-tier universities across Turkey. Founded by passionate educators, we understand the challenges of studying abroad.', 'studyinturkey' ); ?></p>
					<p class="mb-8 text-[15px] leading-relaxed text-gray-500"><?php esc_html_e( 'With years of experience and exclusive partnerships with over 150 universities, we provide end-to-end support—from university selection and application to visa processing and accommodation assistance.', 'studyinturkey' ); ?></p>
					<div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
						<?php foreach ( [
							__( '100% Admission Guarantee', 'studyinturkey' ),
							__( 'Exclusive Scholarship Opportunities', 'studyinturkey' ),
							__( 'End-to-end Visa Support', 'studyinturkey' ),
							__( 'Post-arrival Assistance', 'studyinturkey' ),
						] as $item ) : ?>
							<div class="flex items-center gap-2.5 rounded-xl border border-gray-200/60 bg-white p-3.5">
								<svg class="h-[18px] w-[18px] shrink-0 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
								<span class="text-[13px] font-semibold text-gray-700"><?php echo esc_html( $item ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="relative">
					<div class="absolute -inset-4 rotate-2 rounded-[2rem] bg-brand-600/5"></div>
					<div class="relative h-[480px] overflow-hidden rounded-2xl border border-gray-200/60 shadow-xl">
						<img src="<?php echo esc_url( $img ); ?>" alt="" class="h-full w-full object-cover" />
						<div class="absolute inset-0 bg-gradient-to-t from-[#0a1a1b]/80 to-transparent"></div>
						<div class="absolute bottom-8 left-8 text-white">
							<p class="text-[44px] font-extrabold tracking-tight text-[#ff3131]">10+ <?php esc_html_e( 'Years', 'studyinturkey' ); ?></p>
							<p class="text-[16px] font-medium text-white/70"><?php esc_html_e( 'of Excellence in Education', 'studyinturkey' ); ?></p>
						</div>
					</div>
				</div>
			</div>

			<!-- Stats -->
			<div class="grid grid-cols-2 gap-4 md:grid-cols-4">
				<?php
				$stats = [
					['150+', __('Partner Universities','studyinturkey'), 'text-brand-600', 'bg-[#e6f2f2]', 'border-brand-600/15'],
					['5,000+', __('Successful Students','studyinturkey'), 'text-[#ff3131]', 'bg-red-50', 'border-red-100'],
					['$2M+', __('Scholarships Granted','studyinturkey'), 'text-brand-600', 'bg-[#e6f2f2]', 'border-brand-600/15'],
					['99%', __('Satisfaction Rate','studyinturkey'), 'text-[#ff3131]', 'bg-red-50', 'border-red-100'],
				];
				foreach ( $stats as $s ) : ?>
					<div class="rounded-2xl border <?php echo esc_attr( $s[4] ); ?> bg-white p-7 text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
						<div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl <?php echo esc_attr( $s[3] . ' ' . $s[2] ); ?>">
							<svg class="h-[26px] w-[26px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.562.562 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
						</div>
						<h3 class="mb-1 text-[32px] tracking-tight text-[#0a1a1b]"><?php echo esc_html( $s[0] ); ?></h3>
						<p class="text-[13px] font-medium text-gray-400"><?php echo esc_html( $s[1] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</main>
<?php get_footer();
