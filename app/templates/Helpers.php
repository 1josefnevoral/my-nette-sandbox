<?php

/**
 * Abstract class for template helpers
 *
 * @author     Petr Heinz
 * @package    Cedulart.cz
 */
abstract class Helpers
{

	/**
	 * HelperLoader
	 */
	public static function loader($helper)
	{
		$callback = callback(__CLASS__, $helper);
		if ($callback->isCallable()) {
			return $callback;
		}
	}

	/**
	 * Resizes image and saves it with suffix '-w$w-h$h'
	 * @param string image (filename)
	 * @param string path
	 * @param int w (width)
	 * @param int h (height)
	 * @return string name of newly created file
	 */
	public static function resizeImage($filename, $path, $w, $h=null, $enlarge=false)
	{
		if (empty($filename)||!file_exists($path.$filename)) {
			return false;
		}
		$newFilename = $filename.'-w'.(is_null($w)?'-null':$w).'-h'.(is_null($h)?'-null':$h).'.jpg';
		if(!file_exists($path.$newFilename)){
			$img = Nette\Image::fromFile($path.$filename);
			$originalWidth = $img->getWidth();
			$img->resize($w, $h, $enlarge ? Nette\Image::ENLARGE : 0);
			if($originalWidth > $w) $img->sharpen();
			$img->save($path.$newFilename, 85, Nette\Image::JPEG);
		}
		return $newFilename;
	}

	/**
	 * Gets nicer date format
	 * @param mixed date (MySQL DateTime or timestamp)
	 * @param bool withoutTime (returns only date without time)
	 * @return string
	 */
	public static function formatDate($date, $withoutTime = false)
	{
		if(!is_int($date)) $date = strtotime($date);
		switch(Nette\Environment::getVariable('lang')){
			case('cs'):
				if ($withoutTime) {
					return date('j. n. Y',$date);
				}
				return date('j. n. Y G:i',$date);
			default:
				if ($withoutTime) {
					return date('j. n. Y',$date);
				}
				return date('Y/m/d H:i',$date);
		}
	}

	public static function formatDateWithoutTime($date)
	{
		return self::formatDate($date, true);
	}

	/**
	 * Returns time in words
	 * @todo Multilanguage
	 * @param timestamp $time
	 */
	public static function timeAgoInWords($time)
	{
        if (!$time) {
            return FALSE;
        } elseif (is_numeric($time)) {
            $time = (int) $time;
        } elseif ($time instanceof DateTime) {
            $time = $time->format('U');
        } else {
            $time = strtotime($time);
        }
        $delta = time() - $time;

        if ($delta < 0) {
            $delta = round(abs($delta) / 60);
            if ($delta == 0) return 'za okamžik';
            if ($delta == 1) return 'za minutu';
            if ($delta < 45) return 'za ' . $delta . ' ' . self::plural($delta, 'minuta', 'minuty', 'minut');
            if ($delta < 90) return 'za hodinu';
            if ($delta < 1440) return 'za ' . round($delta / 60) . ' ' . self::plural(round($delta / 60), 'hodina', 'hodiny', 'hodin');
            if ($delta < 2880) return 'zítra';
            if ($delta < 43200) return 'za ' . round($delta / 1440) . ' ' . self::plural(round($delta / 1440), 'den', 'dny', 'dní');
            if ($delta < 86400) return 'za měsíc';
            if ($delta < 525960) return 'za ' . round($delta / 43200) . ' ' . self::plural(round($delta / 43200), 'měsíc', 'měsíce', 'měsíců');
            if ($delta < 1051920) return 'za rok';
            return 'za ' . round($delta / 525960) . ' ' . self::plural(round($delta / 525960), 'rok', 'roky', 'let');
        }

        $delta = round($delta / 60);
        if ($delta == 0) return 'před okamžikem';
        if ($delta == 1) return 'před minutou';
        if ($delta < 45) return "před $delta minutami";
        if ($delta < 90) return 'před hodinou';
        if ($delta < 1440) return 'před ' . round($delta / 60) . ' hodinami';
        if ($delta < 2880) return 'včera';
        if ($delta < 43200) return 'před ' . round($delta / 1440) . ' dny';
        if ($delta < 86400) return 'před měsícem';
        if ($delta < 525960) return 'před ' . round($delta / 43200) . ' měsíci';
        if ($delta < 1051920) return 'před rokem';
        return 'před ' . round($delta / 525960) . ' lety';
    }

    /**
     * Plural: three forms, special cases for 1 and 2, 3, 4.
     * (Slavic family: Slovak, Czech)
     * @param  int
     * @return mixed
     */
    private static function plural($n)
	{
        $args = func_get_args();
        return $args[($n == 1) ? 1 : (($n >= 2 && $n <= 4) ? 2 : 3)];
    }

	public static function truncateHtml($text, $length = 150, $ending = '...', $exact = false, $considerHtml = false)
	{
		if ($considerHtml) {
			if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}

			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

			$total_length = strlen($ending);
			$open_tags = array();
			$truncate = '';

			foreach ($lines as $line_matchings) {
				if (!empty($line_matchings[1])) {
					if (preg_match('/^<(s*.+?/s*|s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(s.+?)?)>$/is', $line_matchings[1])) {
					} else if (preg_match('/^<s*/([^s]+?)s*>$/s', $line_matchings[1], $tag_matchings)) {
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false) {
							unset($open_tags[$pos]);
						}
					} else if (preg_match('/^<s*([^s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}
					$truncate .= $line_matchings[1];
				}
				$content_length = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length+$content_length > $length) {
					$left = $length - $total_length;
					$entities_length = 0;
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
						foreach ($entities[0] as $entity) {
							if ($entity[1]+1-$entities_length <= $left) {
								$left--;
								$entities_length += mb_strlen($entity[0]);
							} else {
								break;
							}
						}
					}
					$truncate .= mb_substr($line_matchings[2], 0, $left+$entities_length);
					break;
				} else {
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}
				if($total_length >= $length) {
					break;
				}
			}
		} else {
			if (mb_strlen($text) <= $length) {
				return $text;
			} else {
				$truncate = mb_substr($text, 0, $length - mb_strlen($ending));
			}
		}
		if (!$exact) {
			$spacepos = mb_strrpos($truncate, ' ');
			if (isset($spacepos)) {
					$truncate = mb_substr($truncate, 0, $spacepos);
			}
		}
		$truncate .= $ending;
		if($considerHtml) {
			foreach ($open_tags as $tag) {
				$truncate .= '</' . $tag . '>';
			}
		}
		return $truncate;
	}

	public static function entityDecode($string)
	{
		return html_entity_decode($string, ENT_COMPAT, 'UTF-8');
	}

	public static function urlencode($string)
	{
		return urlencode($string);
	}

	public static function minify($string)
	{
		$string = preg_replace('/\n/', '', $string);
		$string = preg_replace('/\r/', '', $string);
		return $string;
	}
	public static function round($value, $precision)
	{
		return round($value, $precision);
	}
	public static function count($value)
	{
		return count($value);
	}
}