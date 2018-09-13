<?php

	
function filter($text)
	{
		$result  = new filter($text);
		return $result;
	}	
	
	class filter
	{
			
		const TAGBEGIN = "\x01"; // признак начала тега
		const TAGEND = "\x02"; // признак конца тега
		
		const NOBRSPACE = "\x03";
		const NOBRHYPHEN = "\x04";
		const THINSP = "\x05";
		const DASH = "\x06";
		const NUMDASH = "\x07";
		
		const PUNCT = '\.|,|\!|\?|…|;|\:'; // знаки пунктуации
		const SPACE = '\&nbsp;|\s'; //все пустые символы
		
		const TEXTSPACE = ' | |\t';
		
		const PARAGRAF = "\n[\r]?";
		
		const TAGALL = '</?[a-z]+[^>]*?>'; //цепочка всевозможных тегов
		
		
		
		//экранирование тегов
		var $refs = array();
		var $refscntr = 0;		
				
		var $tags = '';
		var $text = '';
		
		
		function __construct($text = '')
		{
			$this->text = $text;
		}
				
		
		function source($text)
		{
			$this->text = trim($text);
			//$this->text = str_replace("'",'"',$this->text); 
			return $this;
		}
				
		
		function __toString()
		{
			return $this->text;
		}
		
		
		//преобразовываем теги
		private function putTag($x)
		{
			$this->refs[] = $x[0];
			return filter::TAGBEGIN.($this->refscntr++).filter::TAGEND;
		}
	
		//возвращаем теги на место
		private function getTag($x)
		{
			return $this->refs[$x[1]];
		}	
		
		
			
		//преобразовываем теги
		private function putTag1($x)
		{
			$this->refs[] = $x[0];
			return filter::TAGBEGIN;
		}
		
		
		

	
			
		
		private function retag($replace)
		{
					
			if ($replace) //вырезаем теги
				$this->text = preg_replace_callback('/<(?:[^\'"\>]+|".*?"|\'.*?\')+>/s', array($this, 'putTag'), $this->text);
			else {
				$revers = '/'.filter::TAGBEGIN.'(\d+)'.filter::TAGEND.'/';
										
				while(preg_match($revers, $this->text)) {
					$this->text = preg_replace_callback($revers, array($this, 'getTag'), $this->text);
				}	
			}

			return $this;	
					
		}	
		
		function html()
		{
			//вырезаем все теги
			$this->retag(True);
			
					
			//замена на кавычки-елочки
			$this->quotes();
			
			//возвращаем
			$this->retag(False);
			
			return $this;
			
		}
		
		
		//получаем короткий текст с учетом абзацев, в случае неудачи возвращаем null
		function short($len, $array=False)
		{
			
			if ($len >= mb_strlen($this->text))
				return null;
			
			
			//$this->entity('text'); //преобразовываем сущности
							
			$nn = 0; //текущий сивол кода
			$currlen = 0; //текущий символ текста
			$op_tag = False;
									
			$nn = 0;
			while ($currlen < $len) { //находим символ для обреза
				$s = mb_substr($this->text, $nn , 1);
				$nn ++; //текущий символ 
				if ($s=='<') {//открытый тег
					if($op_tag) //это был не тег
						$currlen = $nn;
					$op_tag = True;
				}	
 				else if ($s == '>') //закрытие тега	{
					$op_tag = False;
				else { 
					if (!$op_tag)
						$currlen ++;
				}	
			}
			
									
			$tags = array('</p>', '<br />', '<br>', '</pre>'); //теги окончания абзаца
				
			$currend = null;
				
			$van_str = mb_substr($this->text, 0, $nn); // начало текста
			$obrezok = mb_substr($this->text, $nn); //вторая часть
			$len_obrezok = mb_strlen($obrezok); //кол-во символов после последнего символа
			$currend = $len_obrezok;
			
			foreach ($tags as $tag) { //ищем признак окончания абзаца
				$tag_pos = strpos($obrezok, $tag);
				if ($tag_pos > 0 and $currend > $tag_pos) { //выбираем самый близкий закрывающий тег
					$currend = $tag_pos; //позиция тега
					$taglen = mb_strlen($tag); //размер тега
				}	
			}
									
					
			
			
			if ($currend < $len_obrezok) {
				$endpos = $currend + $taglen; //узнаем последний символ с закрывающим тегом
				if ($endpos == $len_obrezok)
					return null;
				$shorttext =  $van_str.mb_substr($obrezok, 0, $endpos); //получаем обрезанный текст
			}	
			else
				return null;
				
			if ($array == False)
				return $shorttext;
			else {
				$result['shorttext'] = $shorttext;
				$currpos = $nn + $endpos; //определяем вторую часть текста
				$result['moretext'] = mb_substr($this->text, $currpos);
				return $result;
			}
					
						
		}
		
		
		
		
		//корректный перевод строки
		private function correct_return()
		{
					
			if (strtoupper(substr(PHP_OS,0,3)) === 'WIN')
				$this->correct_return = "\r\n"; // перевод строки для Windows-систем
			else
				$this->correct_return = "\n"; // перевод строки для UNIX-систем
					
		}
		
			
		
		function text($entity = 'text')
		{
			
			$this->entity('cp1251');
			$this->html2text();
			$this->space_text(); # убиваем лишние пробелы
			$this->special(); # спецзнаки 
			//$this->text_edit();
			
			if ($entity = 'text')
				return $this;
			else if ($entity = 'xml')
				$this->entity('xml');
			else if	($entity = 'html')
				$this->entity('html');
					
			$this->html2text();
			$this->space_text(); # убиваем лишние пробелы
			
			
		return $this;

		}
		
		
		function xml()
		{
					
			$this->strip_bak_tag(); # лишние и вредоносные теги
			$this->clean();//убиваем стайлы
			$this->space(); # убиваем лишние пробелы
			$this->space_text();
			$this->special(); # спецзнаки 
			$this->html_edit();
			$this->entity('cp1251');
			//$this->text_edit();
			$this->xmltags(); # оставляем тока разрешенные теги
			$this->retag(True); # выкусываем теги
			$this->entity('xml'); # преобразовываем  сущности не затрагивая теги
			$this->retag(False); # теги обратно 
									
			return $this;
		
		}
		
		
		function plaintext()
		{
			return $this;
		}
		
		
		
		
		
		
		function html2()
		{
			
			$this->space(); # убиваем лишние пробелы
			$this->strip_bak_tag(); # лишние и вредоносные теги
			$this->clean();//убиваем стайлы
			$this->special(); # спецзнаки 
			$this->html_edit();
			$this->entity('cp1251');
			//$this->text_edit();
			$this->retag(True); # выкусываем теги
			$this->entity('html'); # преобразовываем  сущности не затрагивая теги
			$this->retag(False); # теги обратно 
			
			return $this;
			
		}
		
			
		function entity_tiny()
		{
			
			//удаление  html-комментариев
			$this->text = preg_replace('/<!--.*-->/Uis', '', $this->text); 			
			
			$this->retag(True); # выкусываем теги
			$this->entity('html'); # преобразовываем  сущности не затрагивая теги
			$this->retag(False); # теги обратно 

			return $this->text;		
			
		}		
				
		
		#отделяем аперанд от сущностей
		function entity_amp()
		{
			$this->text =  preg_replace('"/\&(?![^&][^;]+;)/u"', '&amp;', $this->text);
			return $this;
			
		}
		
		
		
			
		
		//преобразовываемсущности  в рамках HTML
		function entity($type, $amp = False)
		{
					
			if ($type == 'cp1251') {
				$this->text = utf8_html_entity_decode($this->text);
			}	
			else if($type == 'html'){
				$this->entity_amp(); //экранируем аперанд от сущностей
				$this->text = utf8_html_entity_encode($this->text);
			}
			else if ($type == 'html_tag') {
				$this->retag(True); # выкусываем теги
				$this->entity_amp(); //экранируем аперанд от сущностей
				$this->text = utf8_html_entity_encode($this->text);
				$this->retag(False); # теги обратно 
			}			
			else if($type == 'xml') {
								
				$this->text = utf8_html_entity_decode($this->text, True);
								
				//с аперандом
				$xmlentsamp = array(
					"\x26"=>'&amp;',
					"\x22"=>'&amp;quot;',
					"\x3c"=>'&amp;lt;',
					"\x3e"=>'&amp;gt;',
					'   '=>'&amp;#160;'
					);
						
				//без аперанда
				$xmlents = array(
					"\x26"=>'&amp;',
					"\x22"=>'&quot;',
					"\x3c"=>'&lt;',
					"\x3e"=>'&gt;',
					'   '=>'&#160;'
					);
			
							
				if ($amp) {
					foreach ($xmlentsamp as $key=>$element)
						$this->text = str_replace($key, $element, $this->text);
				}
				else { // без аперанда
					foreach ($xmlents as $key=>$element)
						$this->text = str_replace($key, $element, $this->text);
				}			
			}			
							
			return $this;
		
		}	
		
			
		
		//преобразовываем p в ик 
		function p2br($col = 1)
		{
			
			$br = '<br />';
			
			if ($col == 1)
				$r = $br;
			else {
				for ($i = 1; $i <= $col; $i++) 
					$r .= $br;
			}			
			
			$this->text  = preg_replace("!<p[^>]*?>(.*?)</p>!si","\\1".$r, $this->text);
			//плохие теги
			$this->text  = preg_replace("'</?p[^>]*?>'",$r, $this->text);
			
			//брейки в начале
			$this->text  = preg_replace("'^(<br[^>]*>)+'",'', $this->text);
			
			//брейки в конце
			$this->text  = preg_replace("'(<br[^>]*>)+$'",'', $this->text);
			
			return $this;
						
		}
		
		
		
		
		function delete_gap_tag()
		{
			$this->text = preg_replace_callback('/(<s*/*s*)([a-z]+)(s*>)/im', create_function('$tag', 'return preg_replace("/s/i", "", $tag[0]);'), $this->CONTENT[$this->id]);

		return $this->text;
		
		}					
		
		
		function preg_rec($preg, $repl)
		{
			while (preg_match($preg, $this->text)) 
				$this->text = preg_replace($preg, $repl, $this->text);
				
					
			return $this->text;	
		}

		function text2p($type = 'p') {
		
			$currtext = '<p>'.$this->text;
			
			$txtlen = strlen($currtext) - 1;
					
			$p_open = True; //открыт ли тег
			$pos = 0;
					
			while ($pos !== False) {
				
				$pos = strpos($currtext, chr(13).chr(10), $pos);
				
				if ($pos !== False) {
					$rep_pos = $pos;
					$pos = $pos + 2;
					if ($txtlen == $pos) {
						$rep_simbol = '</p>';
						$p_open = False;
					}
					else {	
						$rep_simbol = '</p><p>';
						$p_open = True;
					}	
					$currtext = substr_replace($currtext, $rep_simbol, $rep_pos, 1);
				}
							
			}
								
			if ($p_open)
				$currtext = $currtext.'</p>';
							
			$this->text = $currtext;

			return $this;	
		
		}	
	
		
		function space_text()
		{
									
			// Убираем лишние пустые символы		
			$this->text = preg_replace( '"('.filter::TEXTSPACE.')+"', ' ', $this->text ); 
						
			//пустые символы в начале строки 
			$this->text = preg_replace('"^('.filter::TEXTSPACE.')+"', '',$this->text); 			
						
			//пустые символы в конце строки 
			$this->text = preg_replace('"('.filter::TEXTSPACE.')+$"', '',$this->text);
			$this->text = preg_replace('/['.filter::TEXTSPACE.']+('.filter::PARAGRAF.')/', '\\1', $this->text); 			
			$this->text = preg_replace('/('.filter::PARAGRAF.')['.filter::TEXTSPACE.']+$/', '\\1', $this->text); 						
			$this->text = preg_replace('/['.filter::TEXTSPACE.']+('.filter::PARAGRAF.')/', "\\1", $this->text);
			$this->text = preg_replace('/('.filter::PARAGRAF.')['.filter::TEXTSPACE.']+/', "\\1", $this->text);
			
			return $this;
		
		}
		
		
		
		function space()
		{
			
				
			
			$nospace_tags = 'span|b|strong|i|em|a'; //теги с нежелетаелтельными начальными и конечными пробелами
			
					
			// кон. и нач. пробелы за пределы тега
			$this->text = preg_replace( '!(<('.$nospace_tags.')[^>]*?>)(('.filter::SPACE.')+)!si', '\\3\\1', $this->text );
			$this->text = preg_replace( '!(('.filter::SPACE.')+)(</('.$nospace_tags.')>)!si', '\\3\\1', $this->text );
			
			//пробелы в начале цепочки тегов
			$this->text = $this->preg_rec('!^(('.filter::TAGALL.')+)('.filter::SPACE.')+!si', '\\1', $this->text);
			
			//пробелы в конце цепочки тегов
			$this->text = $this->preg_rec('!('.filter::SPACE.')+(('.filter::TAGALL.')+)$!si', '\\2', $this->text);
	
						
			# -------------------  парагарфы и абазцы --------------------------------------------------------------------
			
			$paragraph = 'p|pre|h1|h2|h3|h4|h5|h6|address|blockquote|center|li';
					
			//бессмысленные пробелы перед br 
			$this->text = preg_replace('!('.filter::SPACE.')+(<br ?/?>)!si', '\\2', $this->text);
					
			//бессмысленные пробелы после br 
			$this->text = preg_replace('!(<br ?/?>)('.filter::SPACE.')+!si', '\\1', $this->text);
								
						
			//бессмысленные пробелы перед параграфом 
			$this->text = preg_replace('!('.filter::SPACE.')+(<'.$paragraph.'[^>]*?>)!si', '\\2', $this->text);
						
						
			//бессмысленные пробелы после параграфа 
			$this->text = preg_replace('!(<\/('.$paragraph.')>)('.filter::SPACE.')+!si', '\\1', $this->text);
									
			//бессмысленные пробелы в начале параграфа 
			$this->text = preg_replace('!(<('.$paragraph.')[^>]*?>)('.filter::SPACE.')+!si', '\\1', $this->text);	
			
			//пробелы в конце параграфа
			$this->text = preg_replace('!<('.$paragraph.')[^>]*?>(.*?)('.filter::SPACE.')+</\\1>!si', '<\\1>\\2</\\1>', $this->text);

			//бессмысленные пробелы в начале параграфа в куче тегов
			$this->text = $this->preg_rec('!(<('.$paragraph.')[^>]*?>)(('.filter::TAGALL.')+)('.filter::SPACE.')+!si', '\\1\\3', $this->text);		
			
			//бессмысленные пробелы в конце параграфа  в куче тегов
			$this->text = $this->preg_rec('!('.filter::SPACE.')+(('.filter::TAGALL.')+)(</'.$paragraph.'>)!si', '\\2\\3', $this->text);	
									
			
			# ---------- дочищаем хвосты ---------------------------------
			
			//убиваем пустые теги
			$this->text = preg_replace('!<([a-z]+)[^>]*?><\/\\1>!si', '', $this->text);
				
			// Убираем лишние пустые символы		
			$this->text = preg_replace( '/[\s]+/', ' ', $this->text ); 
			
			//если попадает &nbsp;		
			$this->text = preg_replace( "'[\s]*(\&nbsp;)+[\s]*'", '&nbsp;', $this->text); 
			
			//удаление начальных пробелов строки 
			$this->text = preg_replace('"^('.filter::SPACE.')+"', '',$this->text); 			
			 		
			//удаление пробелов в конце строки
			$this->text = preg_replace('"('.filter::SPACE.')+$"', '',$this->text);
						
						
			return $this->text;
						
		
		}
		
				
		
		// теги, которые вырезаются вместе с содержимым
		function strip_bak_tag()
		{
			$tags = 'script|style|pre|code|textarea';
/*			$tags .= 'applet|base|basefont|bgsound|blink|body|embed|frame|frameset|head|ilayer|layer|link|meta|object|title|input|form';*/
			$this->text = preg_replace('/< *('.$tags.').*?>.*?< *\/ *\1 *>/is', '', $this->text);
			
			return $this;
			

		}
				
		
		
		
		function html_edit()
		{
			
			//удаление  html-комментариев
			$this->text = preg_replace('/<!--.*-->/Uis', '', $this->text); 			
			
			
			//$this->text = preg_replace( "'([а-яА-ЯёЁa-zA-Z]+)-([а-яА-ЯёЁa-zA-Z]+)'", '<nobr>$1-$2</nobr>', $this->text); //не обрывать слова, написанные через дефис
		
			
			$this->setUrls();//ссылки превращаем в ссылки
			
			$this->text = preg_replace('"\n[\r]?"', '<br />', $this->text);
						
			//праивльное обозначение размера
			$this->text = preg_replace( '~(\d+)[x|X|х|Х|*](\d+)~','$1&times;$2', $this->text );
			
			//все за пределы ссылок
			$this->text = preg_replace( '/<a +href([^>]*)>(['.filter::PUNCT.']+)/', '\\2<a href\\1>', $this->text );
			$this->text = preg_replace( '/(!\.\.|\?\.\.)<\/a>/', '</a>\\1', $this->text );
			$this->text = preg_replace( '/(['.filter::PUNCT.']+)<\/a>/', '</a>\\1', $this->text );
			
								
					
									
			return $this;
		
		}
		
		
		// теги, которые вырезаются вместе с содержимым
		function replacetag($stags)
		{
			$tags = split(',',$stags);
			$nn = 0;
			
			foreach ($tags as $tag){
				$nn++;
				if($nn > 0) $tg .= '|';
				$tg .= trim($tag);
			}	
			
			$this->text = preg_replace('/< *('.$tg.').*?>.*?< *\/ *\1 *>/is', '', $this->text); 
			
			return $this;
		
		}
					
						
		
		// Расстановка кавычек-"елочек"
		function quotes()
		{
			$this->text = preg_replace( '/(['.TAGEND.'\(  ]|^)"([^"]*)([^  "\(])"/', '\\1«\\2\\3»', $this->text ); 
			if (stristr($this->text, '"' )) // Если есть вложенные кавычки
			{
				$text = preg_replace( '/(['.TAGEND.'(  ]|^)"([^"]*)([^  "(])"/', '\\1«\\2\\3»', $this->text );
				while( preg_match( '/«[^»]*«[^»]*»/', $this->text ) )
					$text = preg_replace( '/«([^»]*)«([^»]*)»/', '«\\1„\\2“', $this->text);
			}
			
			return $this;
		
		}
		
		
		
		function text_edit()
		{
				
			
			$this->text = str_replace( '',' ', $this->text ); //косяки верстки 
			$this->text = str_replace( '',' ', $this->text );
									
			$this->text = preg_replace('[^:print:]', '',$this->text); //вырезаем непечатные символы 
			
			
			
			$this->quotes();
			
			
			//знаки припинания
			//$this->text = preg_replace('"'.filter::PUNCT.'?('.filter::PUNCT.')"','\\1', $this->text ); 
		//	$this->text = preg_replace('"(\w+)['.filter::PUNCT.'](\w+)"','\\1, \\2', $this->text);
				
			
			
			//$this->text = $this->specialchars(0); //замены сущностей
						
			//работа с текстом
			
			$this->text = preg_replace( "'\.{3}'", '…', $this->text);  //Многоточие
			
			// $this->text = preg_replace("'\.+'",'.',$this->text); // двойные точки
			$this->text = preg_replace("'\,+'",',',$this->text); // двойные запятые
			$this->text = preg_replace("'\;+'",';',$this->text); // двойные ;
			$this->text = preg_replace("'\:+'",':',$this->text); // двойные :
			$this->text = preg_replace( "' +'", ' ', $this->text); // Убираем лишние пробелы
			$this->text = preg_replace( "'\t+'", ' ', $this->text); // Убираем лишние табуляторы
			
			$this->text = str_replace( ','.NOBRSPACE.DASH.' ',','.DASH.' ', $this->text );
			$this->text = str_replace( '.'.NOBRSPACE.DASH.' ','.'.DASH.' ', $this->text );
			
			
			// пробелы
			$this->text = preg_replace( '/\( *([^)]+?) *\)/', '(\\1)', $this->text ); // удаляем пробелы после открывающей скобки и перед закрыващей скобкой
			$this->text = preg_replace( '/([а-яА-ЯёЁa-zA-Z.,!?:;…])\(/', '\\1 (', $this->text ); // добавляем пробел между словом и открывающей скобкой, если его 
  
			  
			// Русские денежные суммы, расставляя пробелы в нужных местах.
			$this->text = preg_replace( '~(\d+)\s?(руб.)~s','$1 $2', $this->text );
			$this->text = preg_replace( '~(\d+)\s?(млн.|тыс.){1}\s?(руб.)~s','$1 $2 $3', $this->text );
  
						 
			//неразрывать
			$this->text = preg_replace( "'(\w\.)\s?(\w\.)\s(\w\w+)'", '$1 $2 $3', 	$this->text ); // Инициалы + фамилия
			$this->text = preg_replace( "'(\w\w+)\s?(\w\.)\s(\w\.)'", '$1 $2 $3', $this->text ); // фамилия + инициалы
			$this->text = preg_replace("'(\W\w\.)\s(\w\w+)'",'$1 $2',$this->text); //один инициал + фамилия  
   
			//последние обработки
			$this->text = str_replace( '!?','?!', $this->text ); // Правильно в таком порядке
			$this->text = str_replace( '№ №', '№№', $this->text ); // слитное написание "№№"
			$this->text = str_replace( '§ §', '§§', $this->text ); // слитное написание "§§"
 						
					
			return $this;
		}
		
			
		
		
				
		
		
		function strip($tags)
		{
			$this->text = preg_replace("!<р[^>]*?>!si",'',$this->text);	
			return $this;
		
		}
		
		function replace_tag($t1, $t2)
		{
					
			$this->text = preg_replace("!<".$t1."[^>]*?>(.*?)</".$t1.">!usi","<".$t2.">\\1</".$t2.">",$this->text);	
			
			return $this;
		}
		
		
		
		// замена обилие тегов на разрешенные в xml или свои
		function xmltags()
		{
							
			$this->replace_tag('div','p');
			$this->replace_tag('strong','b');
			$this->replace_tag('em','i');
			
								
			$this->text = strip_tags($this->text, '<p>, <br>, <li>, <table>, <tr>, <td>, <ol>, <ul>, <i>, <b>, <iframe>');
			
			return $this;
			
		}
		
				
		//превращаем в ссылки
		private function setUrls()
		{
				
			$prefix='(http|https|ftp|telnet|news|gopher|file|wais)://';
			$pureUrl='([[:alnum:]/\n+-=%&:_.~?]+[#[:alnum:]+]*)';
			$this->text=eregi_replace($prefix.$pureUrl, '<a href="\\1://\\2">\\1://\\2</a>', $this->text); 
		
			return this;
		}
		
				
		//спецсимволика 
		function special($revers = True)
		{
			
			$special = array(
			'(tm)'=>'™', '(TM)'=>'™',
			'(r)'=>'®', '(R)'=>'®',
			'(c)'=>'©', '(C)'=>'©', '(с)'=>'©', '(С)'=>'©',
			'EUR'=>'€', 'eur'=>'€',
			'+/-'=>'±');
			
			if ($revers) // на спецзнаки
				$this->text =  strtr($this->text, $special); 
			else //на обычные
				$this->text =  strtr($this->text, array_flip($special));		
			
			return $this;
			
		}
		
			
			
		//очищаем теги от стилей идов и классов 
		function clean()
		{
			
			$this->text = preg_replace('!<([a-z]+)[\s]+[^>]*?>!si','<\\1>', $this->text);
			
			return $this;	
						
		}
		
				
		function html2text($plain = False)
		{
			
			$this->text = strip_tags($this->text);
			$this->text = trim($this->text);
			return $this;
			
			//$params['wrap'] = 20, $params['br'] = 2;
			
			//преобразования абзацев
			$para_repl = ($plain) ? "\n\n\t" : "\n\n"; 
			$this->text = preg_replace('/<p[^>]*>/ui',$para_repl , $this->text);
						
			
			$this->text = preg_replace('/<br[^>]*>/ui', "\n", $this->text);
							
			$this->text = preg_replace('/<hr[^>]*>/ui',"\n-------------------------\n", $this->text);
						
			//заголовки 
			if ($plain){
				$this->text = preg_replace('/<h[123][^>]*>(.*?)<\/h[123]>/uie', "strtoupper(\"\n\n\\1\n\n\")", $this->text);
				$this->text = preg_replace('/<h[456][^>]*>(.*?)<\/h[456]>/uie', "ucwords(\"\n\n\\1\n\n\")", $this->text);
			}
			else
				$this->text = preg_replace('/<h[123456][^>]*>(.*?)<\/h[123456]>/uie', "strtoupper(\"\n\n\\1\n\n\")", $this->text);			
		
			//преобразования таблицы
			$this->text = preg_replace('/(<table[^>]*>|<\/table>)/ui',"\n\n",$this->text);
			$this->text = preg_replace('/<thead[^>]*>(.*?)<\/thead>/ui', "\\1\n", $this->text); 
			
			if ($plain)
				$this->text = preg_replace('/<th[^>]*>(.*?)<\/th>/ui', "strtoupper(\"\t\\1\")", $this->text); 
			else
				$this->text = preg_replace('/<th[^>]*>(.*?)<\/th>/ui', "\t\\1", $this->text); 	
			
			$this->text = preg_replace('/(<tr[^>]*>|<\/tr>)/ui', "\n", $this->text);
			$this->text = preg_replace('/<td[^>]*>(.*?)<\/td>/ui', "\t\\1", $this->text);
			
			//списки 
			$this->text = preg_replace('/(<ul[^>]*>|<\/ul>)/ui', "\n\n", $this->text);
			$this->text = preg_replace('/(<ol[^>]*>|<\/ol>)/iu', "\n\n", $this->text);
			                 
			$this->text = preg_replace('/<li[^>]*>(.*?)<\/li>/ui', "\t* \\1\n", $this->text);
			
			$this->text = preg_replace('/<li[^>]*>/ui', "\n\t* ", $this->text);
						
			// ссылки 
			$this->text = preg_replace('/<a [^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/uie','\\1',$this->text); 
			
			
			$this->text = trim($this->text);
			
			
			return $this;
			
		}
		
		
		
	
		
			
	}

?>