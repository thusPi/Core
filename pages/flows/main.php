<?php 
	$flows = \thusPi\Flows\get_all();
?>
<div class="btn-column">
	<?php foreach($flows as $flow) : ?>
		<?php $category = new \thusPi\Categories\Category($flow['category']);	?>
		<a 
			tabindex="0" 
			href="#/flows/manage/?id=<?php echo($flow['id']); ?>"
			class="btn btn-tertiary tile transition-fade-order" 
			data-category="<?php echo($flow['category']); ?>" 
			data-page-search="<?php echo($flow['name']); ?>">
			<?php echo(create_icon($flow['icon'], 'xl', ['text-category'])); ?>
			<h3 class="tile-title"><?php echo($flow['name']); ?></h3>
		</a>
	<?php endforeach; ?>
</div>