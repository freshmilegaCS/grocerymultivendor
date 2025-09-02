<footer class="bg-white p-4 relative bottom-0 w-full mt-4">
	<div class="container max-w-7xl mx-auto">
		<div class="flex flex-wrap md:gap-4 lg:gap-0 py-4 mb-6">
			<div class="w-full md:w-full lg:w-1/3 flex flex-col gap-4 mb-6">
				<a class="" href="/">
					<img src="<?= base_url($settings['logo']) ?>" class="rounded-lg w-8" alt="<?= $settings['business_name'] ?>" />
				</a>
				<p><?= $settings['short_description'] ?></p>

				<ul class="flex items-center text-sm gap-4 mt-3">
					<?php $socialLinks = json_decode($settings['social_link'], true); ?>
					<?php foreach ($socialLinks as $social): ?>
						<?php if ($social['status'] == 1): ?>
							<li>
								<a href="<?= htmlspecialchars($social['link']) ?>" target="_blank">
									<i class="<?= htmlspecialchars($social['icon']) ?> text-lg"></i>
								</a>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
				<div class="flex gap-3">


					<?php if (isset($settings['app_url_android']) && $settings['app_url_android'] != null && $settings['app_url_android'] != ''): ?>
						<a target="_blank" href="<?= htmlspecialchars($settings['app_url_android']); ?>">
							<img src="<?= base_url() . 'assets/dist/img/googleplay-btn.svg' ?>" alt="" class="h-8 rounded-lg" />
						</a>
					<?php endif; ?>
					<?php if (isset($settings['app_url_ios']) && $settings['app_url_ios'] != null && $settings['app_url_ios'] != ''): ?>

						<a target="_blank" href="<?= htmlspecialchars($settings['app_url_ios']); ?>">
							<img src="<?= base_url() . 'assets/dist/img/appstore-btn.svg' ?>" alt="" class="h-8 rounded-lg" />
						</a>
					<?php endif; ?>

				</div>
			</div>
			<div class="w-full md:w-full lg:w-2/3">
				<div class="flex flex-wrap">
					<div class="w-1/2 sm:w-1/2 md:w-1/3 flex flex-col gap-4 mb-6">
						<h6 class="text-[22px] font-semibold capitalize"><?php echo lang('website.support'); ?></h6>
						<!-- list -->
						<ul class="flex flex-col gap-2">
							<li><a href="/about-us" class="inline-block hover:text-green-600 text-sm font-medium"><?php echo lang('website.about_us'); ?></a>
							<li><a href="/contact-us" class="inline-block hover:text-green-600 text-sm font-medium"><?php echo lang('website.contact_us'); ?></a>
							<li><a href="/faq" class="inline-block hover:text-green-600 text-sm font-medium"><?php echo lang('website.faq'); ?></a>
						</ul>
					</div>
					<div class="w-1/2 sm:w-1/2 md:w-1/3 flex flex-col gap-4 mb-6">
						<h6 class="text-[22px] font-semibold capitalize"><?php echo lang('website.legal'); ?></h6>
						<ul class="flex flex-col gap-2">
							<li><a href="/privacy-policy" class="inline-block hover:text-green-600 text-sm font-medium"><?php echo lang('website.privacy_policy'); ?></a>
							<li><a href="/terms-condition" class="inline-block hover:text-green-600 text-sm font-medium"><?php echo lang('website.terms_condition'); ?></a>
							<li><a href="/refund-policy" class="inline-block hover:text-green-600 text-sm font-medium"><?php echo lang('website.refund_policy'); ?></a>
						</ul>
					</div>
					<div class="sm:w-1/2 md:w-1/3 flex flex-col gap-4">
						<h6 class="text-[22px] font-semibold capitalize"><?php echo lang('website.conatct'); ?></h6>
						<ul class="flex flex-col gap-2">
							<?php if (isset($settings['phone']) && $settings['phone'] != null && $settings['phone'] != ''): ?>
								<li>
									<a href="tel:<?= htmlspecialchars($settings['phone']); ?>" class="inline-block hover:text-green-600 text-sm font-medium">
										<i class="fi fi-rr-phone-call"></i> <?= htmlspecialchars($settings['phone']); ?>
									</a>
								</li>
							<?php endif; ?>
							<?php if (isset($settings['email']) && $settings['email'] != null && $settings['email'] != ''): ?>
								<li>
									<a href="mailto:<?= htmlspecialchars($settings['email']); ?>" class="inline-block hover:text-green-600 text-sm font-medium">
										<i class="fi fi-rr-envelope"></i> <?= htmlspecialchars($settings['email']); ?>
									</a>
								</li>
							<?php endif; ?>
							<?php
							$location = json_decode($settings['address'], true); // Decode JSON into an associative array
							if (
								isset($location['address']) && $location['address'] != ''
								&& isset($location['latitude']) && $location['latitude'] != ''
								&& isset($location['longitude']) && $location['longitude'] != ''
							):
								$googleMapsLink = "https://www.google.com/maps?q={$location['latitude']},{$location['longitude']}";
							?>
								<li>
									<a href="<?= htmlspecialchars($googleMapsLink); ?>" target="_blank" class="inline-block hover:text-green-600 text-sm font-medium">
										<i class="fi fi-rr-marker"></i> <?= htmlspecialchars($location['address']); ?>
									</a>
								</li>
							<?php endif; ?>


						</ul>

					</div>

				</div>
			</div>
		</div>
		<?php if (isset($settings['footer_text']) && $settings['footer_text'] != null && $settings['footer_text'] != ''): ?>
			<div class="border-t py-4 border-gray-300">
				<div class="gap-y-4 flex flex-wrap items-center justify-center text-sm font-semibold capitalize ">
					<?= htmlspecialchars($settings['footer_text']); ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</footer>