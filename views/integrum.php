<?='<?xml version="1.0" encoding="UTF-8" ?>'?> 
<rss version="2.0" xmlns="http://backend.userland.com/rss2">
 <channel>
	<title><?=$items['ch']['title']?></title> 
	<author><?=$items['ch']['author']?></author> 
	<image>
		<url><?=$items['ch']['image']['url']?></url> 
		<title><?=$items['ch']['image']['title']?></title> 
		<link><?=$items['ch']['image']['link']?></link> 
	</image>
 <description>&quot;Аргументы Недели&quot;, <?=c::$content['dateNumber']?>, №<?=c::$content['numberYear']?> (<?=c::$content['numberAll']?>)</description>
 <?foreach ($items['items'] as $item):?>
	<item>
		<title><?=$filter->source($item['title'])->xml()?></title> 
		<description><?=$filter->source($item['description'])->entity('xml')?></description> 
		<link><?=$item['link']?></link> 
		<pubDate><?=_U2RFC822_modifier($item['pubDate'])?></pubDate>
		<category><?=$item['category']?></category> 
		<?if (isset($item['image_url']) and $item['image_url'] !== ''):?>
			<image> 
				<url><?=$item['image_url']?></url>
				<title><?=$filter->source($item['image_title'])->entity('xml')?></title>
				<link><?=$item['image_link']?></link>
			</image>
		<?endif?>	
		<?if ($item['author']):?>
		<author><?=$filter->source($item['author'])->xml()?></author>
		<?endif?>	
		<full-text><?=$filter->source($item['fulltext'])->html2text()->entity('xml')?></full-text> 
	</item>
	<?endforeach?>
	</channel>
</rss>