<div class='grid'>	
	
	<div class='mtsw s-10-02'>
		<div class='tab'><?=$body?></div>
	</div>
	
	<div class='mtsw s-02'>
		<div class='catalog-menu-wr'>
			<ul class='catalog-menu'>
<?php foreach ($menu AS $item): ?>
				<li<?=$item['act'] ? ' class="act"' : ''?>>
					<a href='<?=$item['href']?>'><?=$item['name']?></a>
<?php 	if (count($item['children'])): ?>
					<ul>
<?php 		foreach ($item['children'] AS $child): ?>
						<li><a href='<?=$child['href']?>'<?=$child['act'] ? ' class="act"' : ''?>><?=$child['name']?></a></li>
<?php 		endforeach; ?>
					
					</ul>
<?php 	endif; ?>
				</li>
<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>	
		
