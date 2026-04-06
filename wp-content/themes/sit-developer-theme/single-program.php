<?php
/**
 * Single Program — Figma: full-width, no sidebar, multi-step form via JS.
 */
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$pid   = get_the_ID();
	$title = sit_theme_get_post_title( $pid );
	$fee   = get_post_meta( $pid, 'sit_tuition_fee', true );
	$dur   = (string) get_post_meta( $pid, 'sit_duration', true );
	$sch   = (bool) get_post_meta( $pid, 'sit_scholarship_available', true );
	$uid   = sit_theme_get_program_university_id( $pid );
	$content = sit_theme_get_post_content_filtered( $pid );

	$uni_title = ''; $uni_link = ''; $logo_url = '';
	if ( $uid > 0 ) {
		$uni_title = sit_theme_get_post_title( $uid );
		$uni_link  = sit_theme_localize_url( get_permalink( $uid ) );
		$lid = (int) get_post_meta( $uid, 'sit_logo_id', true );
		if ( $lid ) $logo_url = wp_get_attachment_image_url( $lid, 'medium' );
	}

	$deg_terms  = get_the_terms( $pid, 'degree_type' );
	$lang_terms = get_the_terms( $pid, 'program_language' );
	$fmt = function( $terms, $tax ) { if ( ! is_array( $terms ) ) return ''; $n=[]; foreach($terms as $t) $n[]=sit_theme_get_term_name((int)$t->term_id,$tax); return implode(', ',array_filter($n)); };
	$deg_line  = $fmt( $deg_terms, 'degree_type' );
	$lang_line = $fmt( $lang_terms, 'program_language' );

	$fee_num = ( is_numeric( $fee ) && (float) $fee > 0 ) ? (float) $fee : null;
	$fee_ref = ( $sch && $fee_num ) ? round( $fee_num * 2, -1 ) : null;
	if ( $fee_ref && $fee_ref <= $fee_num ) $fee_ref = null;

	$prog_url = sit_theme_programs_archive_url();
?>
<main id="main-content" class="flex-1">
	<!-- Top bar -->
	<div class="bg-gradient-to-r from-[#0d4f52] via-brand-700 to-[#0d4f52] px-4 py-6 sm:px-6">
		<div class="sit-container">
			<a href="<?php echo esc_url( $prog_url ); ?>" class="inline-flex items-center gap-2 text-[13px] font-medium text-white/40 transition hover:text-white">← <?php esc_html_e( 'Proqramlar', 'studyinturkey' ); ?></a>
		</div>
	</div>

	<div class="bg-[#f3f6f6] pb-24">
		<div class="sit-container max-w-4xl">
			<!-- Info card -->
			<div class="relative -mt-4 overflow-hidden rounded-2xl border border-gray-200/60 bg-white p-6 shadow-sm md:p-8">
				<div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-brand-600 via-teal-400 to-[#ff3131]"></div>
				<div class="flex flex-col gap-5 md:flex-row md:items-start">
					<?php if ( $logo_url ) : ?>
						<div class="h-20 w-20 shrink-0 rounded-2xl border border-gray-200/60 bg-white p-2 shadow-sm">
							<img src="<?php echo esc_url( $logo_url ); ?>" alt="" class="h-full w-full rounded-xl object-contain" />
						</div>
					<?php endif; ?>
					<div class="flex-1">
						<div class="mb-3 flex flex-wrap gap-1.5">
							<?php if ( $deg_line ) : ?>
								<span class="rounded-full border border-brand-600/15 bg-[#e6f2f2] px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-brand-600"><?php echo esc_html( $deg_line ); ?></span>
							<?php endif; ?>
							<?php if ( $lang_line ) : ?>
								<span class="rounded-full border border-red-100 bg-red-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-[#ff3131]"><?php echo esc_html( $lang_line ); ?></span>
							<?php endif; ?>
						</div>
						<h1 class="mb-2 text-[24px] tracking-[-0.02em] text-[#0a1a1b] md:text-[32px]" style="line-height:1.15"><?php echo esc_html( $title ); ?></h1>
						<?php if ( $uni_link ) : ?>
							<a href="<?php echo esc_url( $uni_link ); ?>" class="flex items-center gap-1.5 text-[14px] font-medium text-gray-400 transition hover:text-brand-600">🏛️ <?php echo esc_html( $uni_title ); ?></a>
						<?php endif; ?>
					</div>
				</div>
				<!-- Specs row -->
				<div class="mt-6 grid grid-cols-2 gap-4 border-t border-gray-100 pt-5 md:grid-cols-4">
					<?php if ( $dur ) : ?>
						<div><p class="mb-1 text-[10px] font-semibold uppercase tracking-[0.1em] text-gray-400">⏱️ <?php esc_html_e( 'Müddət', 'studyinturkey' ); ?></p><p class="text-[15px] font-bold text-[#0a1a1b]"><?php echo esc_html( $dur ); ?></p></div>
					<?php endif; ?>
					<?php if ( $lang_line ) : ?>
						<div><p class="mb-1 text-[10px] font-semibold uppercase tracking-[0.1em] text-gray-400">🌐 <?php esc_html_e( 'Dil', 'studyinturkey' ); ?></p><p class="text-[15px] font-bold text-[#0a1a1b]"><?php echo esc_html( $lang_line ); ?></p></div>
					<?php endif; ?>
					<?php if ( $deg_line ) : ?>
						<div><p class="mb-1 text-[10px] font-semibold uppercase tracking-[0.1em] text-gray-400">🎓 <?php esc_html_e( 'Dərəcə', 'studyinturkey' ); ?></p><p class="text-[15px] font-bold text-[#0a1a1b]"><?php echo esc_html( $deg_line ); ?></p></div>
					<?php endif; ?>
					<div><p class="mb-1 text-[10px] font-semibold uppercase tracking-[0.1em] text-gray-400">📍 <?php esc_html_e( 'Təhsil forması', 'studyinturkey' ); ?></p><p class="text-[15px] font-bold text-[#0a1a1b]"><?php esc_html_e( 'Əyani', 'studyinturkey' ); ?></p></div>
				</div>
			</div>

			<!-- Price block -->
			<?php if ( $fee_num ) : ?>
				<div class="mt-5 relative overflow-hidden rounded-2xl bg-[#0d4f52] p-6 text-white">
					<div class="absolute top-0 right-0 h-64 w-64 rounded-full bg-brand-500/20 blur-[80px]"></div>
					<div class="relative z-10 flex flex-col items-center justify-between gap-5 md:flex-row">
						<div>
							<div class="mb-2 flex items-center gap-2">
								<span class="text-amber-400">✨</span>
								<h3 class="text-[18px] font-bold"><?php esc_html_e( 'Exclusive Scholarship Pricing', 'studyinturkey' ); ?></h3>
							</div>
							<p class="text-[13px] font-medium text-white/40"><?php esc_html_e( 'Apply through us to secure this discounted tuition fee.', 'studyinturkey' ); ?></p>
						</div>
						<div class="w-full rounded-xl border border-white/[0.08] bg-white/[0.06] p-5 backdrop-blur-sm md:w-auto">
							<?php if ( $fee_ref ) : ?>
								<span class="mb-1 block text-[12px] font-medium text-white/30 line-through"><?php esc_html_e( 'Official Fee:', 'studyinturkey' ); ?> $<?php echo esc_html( number_format_i18n( $fee_ref, 0 ) ); ?> / <?php esc_html_e( 'il', 'studyinturkey' ); ?></span>
							<?php endif; ?>
							<span class="text-[28px] font-extrabold tracking-tight text-white">$<?php echo esc_html( number_format_i18n( $fee_num, 0 ) ); ?> <span class="text-[12px] font-medium text-white/40">/ <?php esc_html_e( 'il', 'studyinturkey' ); ?></span></span>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<!-- Content -->
			<?php if ( $content ) : ?>
				<div class="mt-5 rounded-2xl border border-gray-200/60 bg-white p-6 md:p-8">
					<h2 class="mb-4 border-b border-gray-100 pb-4 text-[20px] font-bold text-[#0a1a1b]"><?php esc_html_e( 'Program Overview', 'studyinturkey' ); ?></h2>
					<div class="sit-entry-content text-[15px] leading-relaxed text-gray-500"><?php echo $content; // phpcs:ignore ?></div>
				</div>
			<?php endif; ?>

			<!-- Application form — full width, multi-step via JS wrapper -->
			<div class="mt-8">
				<div class="rounded-2xl border border-gray-200/60 bg-white p-6 shadow-sm md:p-8">
					<div class="mb-1 flex items-center gap-2">
						<span class="text-brand-600">🛡️</span>
						<h2 class="text-[22px] font-bold text-[#0a1a1b]"><?php esc_html_e( 'Müraciət et', 'studyinturkey' ); ?></h2>
					</div>
					<p class="mb-6 text-[13px] font-medium text-gray-400"><?php esc_html_e( 'Formu addım-addım doldurun. Bütün məlumatlar təhlükəsiz saxlanılır.', 'studyinturkey' ); ?></p>

					<!-- Step indicators -->
					<div class="mb-8 flex items-center justify-center gap-0" id="sit-mstep-dots">
						<div class="flex flex-col items-center">
							<span data-mstep-dot="1" class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-600 text-[13px] font-bold text-white shadow-sm">1</span>
							<span class="mt-1.5 text-[10px] font-semibold text-brand-600"><?php esc_html_e( 'Şəxsi', 'studyinturkey' ); ?></span>
						</div>
						<div data-mstep-line="1" class="mx-2 h-0.5 w-12 rounded bg-gray-200 sm:w-20"></div>
						<div class="flex flex-col items-center">
							<span data-mstep-dot="2" class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-[13px] font-bold text-gray-400">2</span>
							<span class="mt-1.5 text-[10px] font-semibold text-gray-400"><?php esc_html_e( 'Təhsil', 'studyinturkey' ); ?></span>
						</div>
						<div data-mstep-line="2" class="mx-2 h-0.5 w-12 rounded bg-gray-200 sm:w-20"></div>
						<div class="flex flex-col items-center">
							<span data-mstep-dot="3" class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-[13px] font-bold text-gray-400">3</span>
							<span class="mt-1.5 text-[10px] font-semibold text-gray-400"><?php esc_html_e( 'Göndər', 'studyinturkey' ); ?></span>
						</div>
					</div>

					<!-- Form wrapper — plugin form gets wrapped by JS -->
					<div id="sit-mstep-form-wrap">
						<?php if ( shortcode_exists( 'sit_application_form' ) ) : ?>
							<?php echo do_shortcode( '[sit_application_form program_id="' . absint( $pid ) . '"]' ); ?>
						<?php else : ?>
							<p class="text-center text-sm text-gray-500"><?php esc_html_e( 'Müraciət formu üçün plugin aktivləşdirin.', 'studyinturkey' ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Back link -->
			<p class="mt-8 text-center">
				<a href="<?php echo esc_url( $prog_url ); ?>" class="text-[14px] font-semibold text-brand-600 transition hover:text-brand-700">← <?php esc_html_e( 'Proqramlar siyahısına qayıt', 'studyinturkey' ); ?></a>
			</p>
		</div>
	</div>
</main>

<script>
(function(){
	var wrap = document.getElementById('sit-mstep-form-wrap');
	if (!wrap) return;
	var form = wrap.querySelector('form');
	if (!form) return;

	// Group children into steps by fieldset/h2/h3 breaks
	var steps = [], cur = [];
	Array.from(form.children).forEach(function(child) {
		var isBreak = child.tagName === 'FIELDSET' || child.tagName === 'H2' || child.tagName === 'H3';
		if (isBreak && cur.length > 0) { steps.push(cur); cur = []; }
		cur.push(child);
	});
	if (cur.length > 0) steps.push(cur);

	// Filter out empty steps (no visible inputs)
	steps = steps.filter(function(elems) {
		return elems.some(function(el) {
			return el.querySelector && (el.querySelector('input,select,textarea') || el.tagName === 'FIELDSET');
		});
	});

	if (steps.length < 2) return;

	// Cap at 3
	if (steps.length > 3) {
		var rest = []; for(var i=2;i<steps.length;i++) rest=rest.concat(steps[i]);
		steps = [steps[0], steps[1], rest];
	}

	// Wrap each step in a div
	var stepDivs = [];
	steps.forEach(function(elems, idx) {
		var div = document.createElement('div');
		div.setAttribute('data-mstep', String(idx+1));
		if (idx > 0) div.style.display = 'none';
		elems.forEach(function(el) { div.appendChild(el); });

		// Nav buttons
		var nav = document.createElement('div');
		nav.className = 'flex gap-3 mt-6';
		if (idx > 0) {
			var prev = document.createElement('button');
			prev.type = 'button';
			prev.className = 'flex-1 rounded-xl border border-gray-200 px-4 py-3.5 text-[14px] font-semibold text-gray-600 transition hover:bg-gray-50';
			prev.innerHTML = '← Əvvəlki';
			prev.addEventListener('click', function() { go(idx); });
			nav.appendChild(prev);
		}
		if (idx < steps.length - 1) {
			var next = document.createElement('button');
			next.type = 'button';
			next.className = 'flex-1 rounded-xl bg-brand-600 px-4 py-3.5 text-[14px] font-bold text-white transition hover:bg-brand-700';
			next.innerHTML = 'Növbəti →';
			next.addEventListener('click', function() {
				// Validate required fields in current step
				var fields = div.querySelectorAll('[required]');
				var valid = true;
				fields.forEach(function(f) {
					f.style.borderColor = '';
					if (!f.value || f.value.trim() === '') {
						f.style.borderColor = '#ff3131';
						valid = false;
					}
				});
				if (!valid) {
					var first = div.querySelector('[required]:invalid, [style*="ff3131"]');
					if (first) first.focus();
					return;
				}
				go(idx+2);
			});
			nav.appendChild(next);
		}
		if (nav.children.length > 0 && idx < steps.length - 1) div.appendChild(nav);
		form.appendChild(div);
		stepDivs.push(div);
	});

	// Hide submit if not last step — move it into last step
	var submit = form.querySelector('button[type="submit"], input[type="submit"]');
	if (submit) {
		var lastStep = stepDivs[stepDivs.length - 1];
		// Wrap submit with prev button
		var finalNav = document.createElement('div');
		finalNav.className = 'flex gap-3 mt-6';
		if (stepDivs.length > 1) {
			var fprev = document.createElement('button');
			fprev.type = 'button';
			fprev.className = 'flex-1 rounded-xl border border-gray-200 px-4 py-3.5 text-[14px] font-semibold text-gray-600 transition hover:bg-gray-50';
			fprev.innerHTML = '← Əvvəlki';
			fprev.addEventListener('click', function() { go(stepDivs.length - 1); });
			finalNav.appendChild(fprev);
		}
		submit.className = 'flex-1 rounded-xl bg-[#ff3131] px-4 py-3.5 text-[14px] font-bold text-white shadow-sm shadow-red-500/20 transition hover:bg-[#e02020] flex items-center justify-center gap-2';
		finalNav.appendChild(submit);
		lastStep.appendChild(finalNav);
	}

	function go(n) {
		stepDivs.forEach(function(d,i) { d.style.display = (i+1===n) ? '' : 'none'; });
		for (var i=1;i<=3;i++) {
			var dot = document.querySelector('[data-mstep-dot="'+i+'"]');
			var line = document.querySelector('[data-mstep-line="'+i+'"]');
			if (dot) {
				dot.className = i<=n ? 'flex h-10 w-10 items-center justify-center rounded-full bg-brand-600 text-[13px] font-bold text-white shadow-sm' : 'flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-[13px] font-bold text-gray-400';
				var lbl = dot.parentElement.querySelector('span:last-child');
				if (lbl) lbl.className = i<=n ? 'mt-1.5 text-[10px] font-semibold text-brand-600' : 'mt-1.5 text-[10px] font-semibold text-gray-400';
			}
			if (line) line.className = i<n ? 'mx-2 h-0.5 w-12 rounded bg-brand-600 sm:w-20' : 'mx-2 h-0.5 w-12 rounded bg-gray-200 sm:w-20';
		}
		wrap.scrollIntoView({behavior:'smooth',block:'start'});
	}
})();
</script>
<?php
endwhile;
get_footer();
