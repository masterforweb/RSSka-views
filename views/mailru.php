<?='<?xml version="1.0" encoding="UTF-8" ?>'?> 
<rss version="2.0" xmlns:mailru="http://news.mail.ru/">
 <channel>
	<title><?=$items['ch']['title']?></title> 
	<author><?=$items['ch']['author']?></author> 
	<image>
		<url><?=$items['ch']['image']['url']?></url> 
		<title><?=$items['ch']['image']['title']?></title> 
		<link><?=$items['ch']['image']['link']?></link> 
	</image>
  <description><?=$items['ch']['image']['description']?></description>
  <?foreach ($items['items'] as $item):?>
	<item>
		<title><?=$filter->source($item['title'])->html2text()->entity('xml')?></title> 
		<description><?=$filter->source($item['description'])->html2text()->entity('xml')?></description> 
		<link><?=$item['link']?></link> 
		<pubDate><?=_U2RFC822_modifier($item['pubDate'])?></pubDate>
		<category><?=$item['category']?></category> 
		<?if ($item['author']):?>
		<author><?=$filter->source($item['author'])->html2text()->entity('xml')?></author>
		<?endif?>	
		<mailru:full-text><?=_CDATA_modifier($filter->source($item['fulltext'])->html2text()->entity('xml'))?></mailru:full-text> 
	</item>
	<?endforeach?>
	</channel>
</rss>