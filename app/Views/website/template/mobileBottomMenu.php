<!-- bottom mobile menu start -->
<div class="fixed bottom-0 w-full bg-white border-t block md:hidden lg:hidden  z-30">
	<div class="flex justify-between p-1 px-2 items-center">
		<a href="/" class="flex flex-col items-center">
			<i class="fi fi-rr-house-chimney"></i>
			<span class="text-xs"><?php echo lang('website.home');?></span>
		</a>
		<a href="/category" class="flex flex-col items-center">
			<i class="fi fi-rr-category-alt"></i>
			<span class="text-xs"><?php echo lang('website.category');?></span>
		</a>
		<a href="/order-history" class="flex items-center justify-center border rounded-full w-14 h-14 bg-green-700 shadow-md relative bottom-3">
			<i class="fi fi-rr-order-history font-bold text-2xl text-white pt-2"></i>
		</a>
		<a href="/notification" class="flex flex-col items-center">
			<i class="fi fi-rr-bells"></i>
			<span class="text-xs"><?php echo lang('website.notification');?></span>
		</a>
		<?php if ((session()->has('email') && session()->get('is_email_verified') == 1) || (session()->has('mobile') && session()->get('is_mobile_verified') == 1)) {
		?>
			<a href="/menu" class="flex flex-col items-center">
				<img class="h-6 w-6 rounded-full ring-2 ring-white" src="<?php
																			echo isset($user)
																				? (($user['login_type'] === 'mobile')
																					? (isset($user['img']) ? $user['img'] : base_url() . $settings['logo']) // mobile login
																					: base_url() . $settings['logo']) // any other login type
																				: base_url() . $settings['logo']; // no user
																			?>
" alt="">
				<span class="text-xs"><?php echo lang('website.menu');?></span>
			</a>
		<?php
		} else {
		?>
			<a href="/menu" class="flex flex-col items-center">
				<i class="fi fi-rr-bars-staggered"></i>
				<span class="text-xs"><?php echo lang('website.menu');?></span>
			</a>
		<?php
		} ?>
	</div>
</div>
<!-- bottom mobile menu end -->