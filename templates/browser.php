<?php if ($before) { ?>
    <a class="prev" href="<?php echo $before['slug']; ?>.html">
	&laquo; <?php echo ($before['number'] ? ($before['number'] . ' - ') : '' ) . $before['chapter']; ?>
    </a>
<?php } ?>
<?php if ($after) { ?>
    <a class="next" href="<?php echo $after['slug']; ?>.html">
	<?php echo $after['number'] . ' - ' . $after['chapter']; ?> &raquo;
    </a>
<?php } ?>
