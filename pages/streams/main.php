<?php 
	$streams = \thusPi\Streams\get_all();
?>
<div class="btn-column">
	<?php foreach($streams as $stream) : ?>
		<?php $category = new \thusPi\Categories\Category($stream['category']);	?>
		<a 
			tabindex="0" 
			href="/#/streams/view/?id=<?php echo($stream['id']); ?>"
			class="btn btn-tertiary tile transition-fade-order" 
			data-category="<?php echo($stream['category']); ?>" 
			data-page-search="<?php echo($stream['name']); ?>">
			<?php echo(create_icon($stream['icon'], 'xl', ['text-category'])); ?>
			<h3 class="tile-title"><?php echo($stream['name']); ?></h3>
		</a>
	<?php endforeach; ?>
</div>