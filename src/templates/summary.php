<div class="summary">
<ul>
<?php foreach ($summary as $entry) { ?>
    <?php if ($entry['number'] == 0) continue; ?>
    <div class="slide middleSlide summarySlide">
    <li>
	<a href="<?php echo $entry['slug']; ?>.html"><?php echo $entry['number']; ?> - <?php echo $entry['chapter']; ?></a>
	<ul>
	<?php foreach ($entry['parts'] as $part) { ?>
	    <li>
		<a href="<?php echo $entry['slug']; ?>.html#part<?php echo $part['number']; ?>">
		    <?php echo $part['number']; ?>) <?php echo $part['title']; ?>
		</a>
	    </li>
	<?php } ?>

	<?php foreach ($entry['annexes'] as $annex) { ?>
	    <li class="annexSummary">
		<a href="<?php echo $annex['slug']; ?>.html">
		    Annexe&nbsp;: <?php echo $annex['title']; ?>
		</a>
	    </li>
	<?php } ?>
	</ul>
    </li>
    </div>
<?php } ?>
</ul>
</div>
