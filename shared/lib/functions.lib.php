<?php
/**
 * Convert date to standard date format: YYYY-MM-DD
 *
 * @param string $date
 * @return string
 */
function standardDate($date) {
	if (empty($date) OR $date=='0000-00-00') return '';
	$date = str_replace(['/', '.'], '-', trim($date, '.'));
	$dt = strtotime($date);
	return date('Y-m-d', $dt);
}

/**
 * Convert date to standard date-time format: YYYY-MM-DD HH:II:SS
 *
 * @param string $timestamp
 * @return string
 */
function standardDateTime($timestamp) {
    if (empty($timestamp) OR $timestamp == '0000-00-00 00:00:00') return '';
    $timestamp = str_replace(['/', '.'], '-', trim($timestamp, '.'));
    $dt = strtotime($timestamp);
    return date('Y-m-d H:i:s', $dt);
}

/**
 * Calculate day difference between 2 dates
 *
 * @param string $date1
 * @param string $date2
 * @return int
 * @throws Exception
 */
function dateDiffDays($date1, $date2) {
	$date1 = new DateTime($date1);
	$date2 = new DateTime($date2);
	$interval = $date1->diff($date2);
	return $interval->format('%r%a');
}

/**
 * Convert minutes (int) to H:i format
 * @param $time
 * @param string $format
 * @return string|void
 */
function convertToHoursMins($time, $format = '%02d:%02d') {
	if ($time < 1) {
		return;
	}
	$hours = floor($time / 60);
	$minutes = ($time % 60);
	return sprintf($format, $hours, $minutes);
}

/**
 * Get how many days is the diff betwen now and the given date
 *
 * @param string $date
 * @return int
 * @throws Exception
 */
function daysAhead($date){
	$now = date('Y-m-d');
	return dateDiffDays($now, $date);
}

/**
 * Check for a given date whether is it in a date range
 *
 * @param string $start_date
 * @param string $end_date
 * @param string $check_date
 * @return bool
 */
function isDateInRange($start_date, $end_date, $check_date){
	$start_ts = strtotime($start_date);
	$end_ts = strtotime($end_date);
	$check_ts = strtotime($check_date);

	return (($check_ts >= $start_ts) && ($check_ts <= $end_ts));
}

/**
 * Check date format validity
 *
 * @param $date
 * @return bool
 */
function validateDate($date){
	$d = DateTime::createFromFormat('Y-m-d', $date);
	return $d && $d->format('Y-m-d') == $date;
}

function getQuarter(\DateTime $DateTime) {
	$y = $DateTime->format('Y');
	$m = $DateTime->format('m');
	$q = 1;
	switch($m) {
		case $m >= 1 && $m <= 3:
			$start = $y . '-01-01';
			$end = (new DateTime($y . '-03-01'))->modify('Last day of this month')->format('Y-m-d');
			$title = 'Q1 ' . $y;
			$q = 1;
			break;
		case $m >= 4 && $m <= 6:
			$start = $y . '-04-01';
			$end = (new DateTime($y . '-06-01'))->modify('Last day of this month')->format('Y-m-d');
			$title = 'Q2 ' . $y;
			$q = 2;
			break;
		case $m >= 7 && $m <= 9:
			$start = $y . '-07-01';
			$end = (new DateTime($y . '-09-01'))->modify('Last day of this month')->format('Y-m-d');
			$title = 'Q3 ' . $y;
			$q = 3;
			break;
		case $m >= 10 && $m <= 12:
			$start = $y . '-10-01';
			$end = (new DateTime($y . '-12-01'))->modify('Last day of this month')->format('Y-m-d');
			$title = 'Q4 ' . $y;
			$q = 4;
			break;
	}

	return array(
		'start' => $start,
		'end' => $end,
		'title' => $title,
		'quarter' => $q,
		'start_nix' => strtotime($start),
		'end_nix' => strtotime($end)
	);
}

function getQuarterPeriods($year, $quarter){
	$out = [];
	switch($quarter){
		case 1:
			$out['start'] = $year . '-01-01';
			$out['end'] = (new DateTime($year . '-03-01'))->modify('Last day of this month')->format('Y-m-d');
			break;
		case 2:
			$out['start'] = $year . '-04-01';
			$out['end'] = (new DateTime($year . '-06-01'))->modify('Last day of this month')->format('Y-m-d');
			break;
		case 3:
			$out['start'] = $year . '-07-01';
			$out['end'] = (new DateTime($year . '-09-01'))->modify('Last day of this month')->format('Y-m-d');
			break;
		case 4:
			$out['start'] = $year . '-10-01';
			$out['end'] = (new DateTime($year . '-12-01'))->modify('Last day of this month')->format('Y-m-d');
			break;
	}

	return $out;
}

function calculateNextDueDate($recurrence, $fromDate = false){
    $date = false;
    $mod = false;

    if(!$fromDate) {
        $fromDate = date('Y-m-d');
    }else{
        $fromDate = standardDate($fromDate);
    }

    switch($recurrence){
        case 1:     // daily
            $mod = '+1 day';
            break;
        case 2:     // 2 weeks
            $mod = '+2 week';
            break;
        case 3:     // monthly
            $mod = '+1 month';
            break;
        case 4:     // quarterly
            $mod = '+3 month';
            break;
        case 5:     // half yearly
            $mod = '+6 month';
            break;
        case 6:     // yearly
            $mod = '+1 year';
            break;
        case 7:     // every 2 years
            $mod = '+2 year';
            break;
        case 8:     // every 4 years
            $mod = '+4 year';
            break;
    }

    if($mod) {
        $date = strtotime($fromDate . ' ' . $mod);
        $date = date('Y-m-d', $date);
    }

    return $date;
}

/**
 * Convert number to regular float format (replace , to .)
 *
 * @param string $str
 * @return string
 */
function floatNumber($str){
	return (float) str_replace(',', '.', $str);
}

/**
 * Add given days to a date
 *
 * @param string $date
 * @param int $days
 * @return string
 * @throws Exception
 */
function dateAddDays($date = 'now', $days = 7) {
	$date = new DateTime($date);
	if ($days > 0) $days = '+' . $days;
	$date->modify($days . " days");
	return $date->format('Y-m-d');
}


/**
 * Check two date range whether they are overlapping each other
 * @param $startDate1
 * @param $endDate1
 * @param $startDate2
 * @param $endDate2
 * @return bool
 */
function checkDatesOverlapping($startDate1, $endDate1, $startDate2, $endDate2){
    return (($startDate1 <= $endDate2) && ($endDate1 >= $startDate2));
}

/**
 * Add zero (0) perfixes to a number for a given length
 *
 * @param int $num the number which need to add zero prefixes
 * @param int $len total length of the result
 * @return string
 */
function fillNulls($num, $len) {
	$l = strlen($num);
	if($len < $l) $len = $l;
	$out = str_repeat('0', $len - $l) . $num;
	return $out;
}

/**
 * Alias for FillNulls
 *
 * @param string $number
 * @param int $length
 * @return string
 */
function addNullPrefix($number, $length) {
	return fillNulls($number, $length);
}

/**
 * Calculate age (in years) from birthdate
 *
 * @param string $birthDate
 * @param bool|string $fromDate if paramter is given, calculation is started from the given date instead of current date
 * @return int
 */
function getAgeFromBirthdate( $birthDate = '', $fromDate = false ){
    $age = false;
	if(!$fromDate) {
		$fromDate = time();
	}else{
		$fromDate = standardDate($fromDate);
		$fromDate = strtotime($fromDate);
		if($fromDate<time()) $fromDate = time();
	}
    $birthDate = explode("-", standardDate($birthDate)); # date parts
    if( !empty($birthDate[0]) ) $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md", $fromDate) ? ((date("Y", $fromDate) - $birthDate[0]) - 1) : (date("Y", $fromDate) - $birthDate[0]));
    # get age in years
    return $age;
 }

/**
 * Calculate age (in month) from birthdate
 *
 * @param string $birthDate
 * @param bool|string $fromDate
 * @return int
 */
function getAgeInMonth($birthDate, $fromDate = false) {
	if(!$fromDate) $fromDate = time();
	$now = strtotime( $fromDate );
	$leapDays = getNumOfLeapDays($birthDate, $now);
	$age = intval( (( ($now - $birthDate)/(3600*24) )-$leapDays)/365 * 12 );
	return $age;
}

/**
 * Get leap days in a given year
 *
 * @param string $from
 * @param string $to
 * @return int
 */
function getNumOfLeapDays($from, $to) {
	if ( (checkdate(2,29,date("Y",$from))) AND ($from > mktime(0,0,0,2,29,date("Y",$from))) ) {
		$fromYear = date("Y",$from)+1;
	}else {
		$fromYear = date("Y",$from);
	}

	if ( (checkdate(2,29,date("Y",$to))) AND ($to<mktime(0,0,0,2,29,date("Y",$to))) ) {
		$toYear = date("Y",$to)-1;
	}else {
		$toYear = date("Y",$to);
	}

	$numOfLeapDays = 0;

	for($i=$fromYear; $i<=$toYear; $i++) {
		if ((($i % 4) == 0) AND ((($i % 400) == 0) OR (($i % 100) <>0))) $numOfLeapDays++;
	}

	return $numOfLeapDays;
}

/**
 * Check email validity
 *
 * @param string $email
 * @param bool $mxLookup
 * @return bool
 */
function checkEmail($email, $mxLookup = false){
	$out = false;

	if(preg_match("/^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,6}$/i", $email)) {
		if ($mxLookup) {
			$tld = substr(strstr($email, '@'), 1);
			if (getmxrr($tld, $email_val) ) $out = true;
			if (checkdnsrr($tld,"ANY")) $out = true;
		} else {
			$out = true;
		}
	} else {
		$out = false;
	}

	return $out;
}

/**
 * Check URL weather is it contains HTTP(S)
 *
 * @param string $url
 * @return string
 */
function checkURL($url){
	$url = strtolower($url);
	if(!Empty($url)){
		if(substr($url, 0, 7)!="http://" AND substr($url, 0, 8)!="https://"){
			$url = "http://".$url;
		}
	}
	return $url;
}

/**
 * Check string encoding for UTF-8
 *
 * @param string $str
 * @return bool
 */
function isUtf8($str) {
	$length = strlen($str);
	for ($i=0; $i < $length; $i++) {
		$c = ord($str[$i]);
		if ($c < 0x80) $n = 0; # 0bbbbbbb
		elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
		elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
		elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
		elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
		elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
		else return false; # Does not match any model
		for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
			if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
				return false;
		}
	}
	return true;
}

/**
 * Remove accents from strings
 *
 * @param string $string
 * @return mixed|string
 */
function removeAccents($string){
	if ( !preg_match('/[\x80-\xff]/', $string) )
		return $string;

	if (isUtf8($string)) {
		$chars = array(
			// Decompositions for Latin-1 Supplement
			chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
			chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
			chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
			chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
			chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
			chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
			chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
			chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
			chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
			chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
			chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
			chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
			chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
			chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
			chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
			chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
			chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
			chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
			chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
			chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
			chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
			chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
			chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
			chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
			chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
			chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
			chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
			chr(195).chr(191) => 'y',
			// Decompositions for Latin Extended-A
			chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
			chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
			chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
			chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
			chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
			chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
			chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
			chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
			chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
			chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
			chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
			chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
			chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
			chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
			chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
			chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
			chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
			chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
			chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
			chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
			chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
			chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
			chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
			chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
			chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
			chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
			chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
			chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
			chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
			chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
			chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
			chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
			chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
			chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
			chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
			chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
			chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
			chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
			chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
			chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
			chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
			chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
			chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
			chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
			chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
			chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
			chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
			chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
			chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
			chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
			chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
			chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
			chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
			chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
			chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
			chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
			chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
			chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
			chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
			chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
			chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
			chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
			chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
			chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
			// Euro Sign
			chr(226).chr(130).chr(172) => 'E',
			// GBP (Pound) Sign
			chr(194).chr(163) => '');

		$string = strtr($string, $chars);
	} else {
		// Assume ISO-8859-1 if not UTF-8
		$chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
			.chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
			.chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
			.chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
			.chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
			.chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
			.chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
			.chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
			.chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
			.chr(252).chr(253).chr(255);

		$chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

		$string = strtr($string, $chars['in'], $chars['out']);
		$double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
		$double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
		$string = str_replace($double_chars['in'], $double_chars['out'], $string);
	}

	return $string;
}

/**
 * Replace cyrillic letters from strings
 *
 * @param string $str
 * @return string
 */
function removeCyrillicLetters($str){
	$contains_cyrillic = (bool) preg_match('/[\p{Cyrillic}]/u', $str);
	if ($contains_cyrillic) {
		$translate = [
			'A' => 'A',
			'Б' => 'B',
			'В' => 'V',
			'Г' => 'G',
			'Д' => 'D',
			'Е' => 'E',
			'Ё' => 'Yo',
			'Ж' => 'Zh',
			'З' => 'Z',
			'И' => 'I',
			'Й' => 'J',
			'K' => 'K',
			'Л' => 'L',
			'М' => 'M',
			'Н' => 'N',
			'O' => 'O',
			'П' => 'P',
			'Р' => 'R',
			'С' => 'S',
			'Т' => 'T',
			'У' => 'U',
			'Ф' => 'F',
			'Х' => 'Kh',
			'Ц' => 'Ts',
			'Ч' => 'Ch',
			'Ш' => 'Sh',
			'Щ' => 'Shch',
			'Э' => 'E',
			'Ю' => 'Yu',
			'Я' => 'Ya',
			'а' => 'a',
			'б' => 'b',
			'в' => 'v',
			'г' => 'g',
			'д' => 'd',
			'е' => 'e',
			'ё' => 'e',
			'ж' => 'zh',
			'з' => 'z',
			'и' => 'i',
			'й' => 'y',
			'к' => 'k',
			'л' => 'l',
			'м' => 'm',
			'н' => 'n',
			'о' => 'o',
			'п' => 'p',
			'р' => 'r',
			'с' => 's',
			'т' => 't',
			'у' => 'u',
			'ф' => 'f',
			'х' => 'kh',
			'ц' => 'ts',
			'ч' => 'ch',
			'ш' => 'sh',
			'щ' => 'shch',
			'ы' => 'y',
			'ь' => '',
			'ъ' => '',
			'э' => 'e',
			'ю' => 'yu',
			'я' => 'ya'
		];

		$str = strtr($str, $translate);
	}

	return $str;
}

/**
 * Remove any special chars from strings, only alpha(numeric) + sapce
 * (accents, dash, underscores)
 *
 * @param string $str
 * @param bool $allow_numbers
 * @param bool $no_cyrillic_letters
 * @return string
 */
function removeSpecialChars($str, $allow_numbers = false, $no_cyrillic_letters = true) {
	if($no_cyrillic_letters) {
		// remove Cyrillic characters
		$str = removeCyrillicLetters($str);
	}

	$str = removeAccents($str);

	//changing - and _ to whitespace
	$pattern = '/[-_]/';
	$str = preg_replace($pattern, ' ', $str);

	//removing specials
	$pattern = '/[^a-zA-Z ]/';
	if ($allow_numbers) $pattern = '/[^0-9a-zA-Z ]/';
	$str = preg_replace($pattern, '', $str);

	return trim($str);
}

/**
 * Convert string to safe URL characters
 *
 * @param string $link
 * @return string
 */
function safeURL($link){
	$link = removeAccents($link);

	//removing specials
	$pattern = '/[^0-9a-zA-Z- _]/';
	$link = preg_replace($pattern, '', $link);

	$link = strtolower(trim($link));

	//changing _ and space to -
	$pattern = '/[_ ]/';
	$link = preg_replace($pattern, '-', $link);

	return urlencode($link);
}

/**
 * Convert string to safe file characters
 *
 * @param string $filename
 * @return string
 */
function safeFileName($filename){
	$filename = trim($filename);
	$filename = removeAccents($filename);

	//removing specials
	$pattern = '/[^0-9a-zA-Z-_. ]/';
	$filename = preg_replace($pattern, '', $filename);

	//changing space to _
	$pattern = '/[ ]/';
	$filename = preg_replace($pattern, '_', $filename);

	return $filename;
}
/**
 * Return only numbers from string
 *
 * @param string $str
 * @return int
 */
function onlyNumbers($str){
	return preg_replace('/\D/', '', $str);
}

/**
 * Return only alphanumeric string
 *
 * @param string $str
 * @return string
 */
function onlyAlphaNumeric($str){
	$str = removeSpecialChars($str, true);
	return preg_replace('/[^a-zA-Z0-9]+/', '', $str);
}

/**
 * Mask credit card numbers with mask char
 *
 * @param string $num
 * @param int $digits_shown
 * @param string $mask_char
 * @return string
 */
function maskCreditCardNumber($num, $digits_shown = 4, $mask_char = '*'){
	$len = strlen($num) - $digits_shown;

	$out = '';

	for($i=1;$i<=$len;$i++){
		$out .= $mask_char;
	}

	$out .= substr($num, $digits_shown * -1);

	return $out;
}

/**
 * Check credit card format validity
 *
 * @param string $cardNumber
 * @param $cardType
 * @return bool|int
 */
function checkCreditCard($cardNumber, $cardType) {

	// Define the cards we support. You may add additional card types.

	//  Name:      As in the selection box of the form - must be same as user's
	//  Length:    List of possible valid lengths of the card number for the card
	//  prefixes:  List of possible prefixes for the card
	//  checkdigit Boolean to say whether there is a check digit

	// Don't forget - all but the last array definition needs a comma separator!

	$cards = array (  array ('name' => 'AX',
		'length' => '15',
		'prefixes' => '34,37',
		'checkdigit' => true
	),
		array ('name' => 'DC',
			'length' => '14,16',
			'prefixes' => '305,36,38,54,55',
			'checkdigit' => true
		),
		/*
		   array ('name' => 'Discover',
				  'length' => '16',
				  'prefixes' => '6011,622,64,65',
				  'checkdigit' => true
				 ),
		   array ('name' => 'Diners Club Enroute',
				  'length' => '15',
				  'prefixes' => '2014,2149',
				  'checkdigit' => true
				 ),
		   */
		array ('name' => 'JC',
			'length' => '16',
			'prefixes' => '35',
			'checkdigit' => true
		),
		array ('name' => 'MC',
			'length' => '12,13,14,15,16,18,19',
			'prefixes' => '5016,5018,5020,5038,6304,6759,6761',
			'checkdigit' => true
		),
		array ('name' => 'CA',
			'length' => '16',
			'prefixes' => '51,52,53,54,55',
			'checkdigit' => true
		),
		/*
		   array ('name' => 'Solo',
				  'length' => '16,18,19',
				  'prefixes' => '6334,6767',
				  'checkdigit' => true
				 ),
		   array ('name' => 'Switch',
				  'length' => '16,18,19',
				  'prefixes' => '4903,4905,4911,4936,564182,633110,6333,6759',
				  'checkdigit' => true
				 ),
		   */
		array ('name' => 'VI',
			'length' => '13,16',
			'prefixes' => '4',
			'checkdigit' => true
		),
		array ('name' => 'E',
			'length' => '16',
			'prefixes' => '417500,4917,4913,4508,4844',
			'checkdigit' => true
		)
		/*
		   array ('name' => 'LaserCard',
				  'length' => '16,17,18,19',
				  'prefixes' => '6304,6706,6771,6709',
				  'checkdigit' => true
				 )
		   */
	);

	// Establish card type
	$cardType = -1;
	for ($i=0; $i<sizeof($cards); $i++) {

		// See if it is this card (ignoring the case of the string)
		if (strtolower($cardType) == strtolower($cards[$i]['name'])) {
			$cardType = $i;
			break;
		}
	}

	// If card type not found, report an error
	if ($cardType == -1) {
		return 1;
	}

	// Remove any spaces from the credit card number
	$cardNo = str_replace (' ', '', $cardNumber);

	// Check that the number is numeric and of the right sort of length.
	//if (!eregi('^[0-9]{13,19}$',$cardNo))  {
	if (!preg_match('/^[0-9]{13,19}$/',$cardNo))  {
		return 2;
	}

	// Now check the modulus 10 check digit - if required
	if ($cards[$cardType]['checkdigit']) {
		$checksum = 0;                                  // running checksum total
		$j = 1;                                         // takes value of 1 or 2

		// Process each digit one by one starting at the right
		for ($i = strlen($cardNo) - 1; $i >= 0; $i--) {

			// Extract the next digit and multiply by 1 or 2 on alternative digits.
			$calc = $cardNo{$i} * $j;

			// If the result is in two digits add 1 to the checksum total
			if ($calc > 9) {
				$checksum = $checksum + 1;
				$calc = $calc - 10;
			}

			// Add the units element to the checksum total
			$checksum = $checksum + $calc;

			// Switch the value of j
			if ($j ==1) {$j = 2;} else {$j = 1;};
		}

		// All done - if checksum is divisible by 10, it is a valid modulus 10.
		// If not, report an error.
		if ($checksum % 10 != 0) {
			return 3;
		}
	}

	// The following are the card-specific checks we undertake.

	// Load an array with the valid prefixes for this card
	$prefix = explode(',',$cards[$cardType]['prefixes']);

	// Now see if any of them match what we have in the card number
	$PrefixValid = false;
	for ($i=0; $i<sizeof($prefix); $i++) {
		$exp = '/^' . $prefix[$i].'/';
		if (preg_match($exp,$cardNo)) {
			$PrefixValid = true;
			break;
		}
	}

	// If it isn't a valid prefix there's no point at looking at the length
	if (!$PrefixValid) {
		return 3;
	}

	// See if the length is valid for this card
	$LengthValid = false;
	$lengths = explode(',',$cards[$cardType]['length']);
	for ($j=0; $j<sizeof($lengths); $j++) {
		if (strlen($cardNo) == $lengths[$j]) {
			$LengthValid = true;
			break;
		}
	}

	// See if all is OK by seeing if the length was valid.
	if (!$LengthValid) {
		return 4;
	};

	// The credit card is in the required format.
	return false;
}

/**
 * Generate random string
 *
 * @param int $length
 * @param bool $addExtraChars
 * @return string
 */
function generateRandomString($length = 10, $addExtraChars = false){
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	if($addExtraChars){
		$characters .= '!$%-';
	}

	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $randomString;
}

/**
 * Credit card formating
 *
 * @param string $cc
 * @param string $div
 * @return string
 */
function formatCreditCardNumber($cc, $div = ' '){
   $cc = str_replace(array('-',' '),'',$cc);
   $cc_length = strlen($cc);
   $newCreditCard = substr($cc,-4);
   for($i=$cc_length-5;$i>=0;$i--){
       if((($i+1)-$cc_length)%4 == 0){
           $newCreditCard = $div.$newCreditCard;
       }
        $newCreditCard = $cc[$i].$newCreditCard;
   }
   return $newCreditCard;
}

/**
 * Deletes a directory's content recursively
 *
 * @param $p_del string (directory path)
 * @param $b bool (at the end remove the directory itself or not)
 * @return void
 */
function delTree($p_del, $b = false ) {
	if (empty($p_del)) return;
	$elv = '/';
	if ( $handle = opendir($p_del . $elv) ) {
		while ( $file = readdir($handle) ) {
			if ( $file == '..' || $file == '.' ) continue;
			if ( is_file($p_del . $elv . $file) ) unlink( $p_del . $elv . $file );
			if ( is_dir($p_del . $elv . $file) ) delTree( $p_del . $elv . $file, true );
		}
		closedir( $handle );
	}
	if ( $b ) @rmdir($p_del);
}

/**
 * Adds array2's content to array1
 *
 * @param $array1 array
 * @param $array2 array
 * @param $overwrite bool
 * @return array
 */
function arrayMerge( $array1, $array2, $overwrite = false ) {
	if (is_array($array2)) {
		foreach ($array2 as $key => $value) {
			if (!isset($array1[$key])) {
				 $array1[$key] = $value;
			} else {
				if (is_array($value)) {
					$array1[$key] = arrayMerge($array1[$key], $value);
				} else {
					if (empty($array1[$key]) || $overwrite) {
						$array1[$key] = $value;
					}
				}
			}
		}
	}
	return $array1;
}

/**
 * Clear Twig cache directory
 *
 * @return void
 */
function clearTwigCache(){
	if (defined('DIR_CACHE')) {
		delTree(DIR_CACHE . 'twig');
	}
}


/**
 * Get top domain name from SERVER global
 *
 * @param string|bool $url
 * @return string
 */
function getMainDomain($url = false){
	if(!$url) {
		if ($_SERVER['HTTP_HOST']) {
			$url = $_SERVER['HTTP_HOST'];
		} else {
			$url = $_SERVER['SERVER_NAME'];
		}
	}

	$pieces = parse_url($url);
	$domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];
	$tmp = explode('.', $domain);
	$l = count($tmp) - 1;
	return $tmp[$l-1].'.'.$tmp[$l];
}

/**
 * Check remote file whether is it exists
 *
 * @param string $url
 * @return bool
 */
function checkRemoteFile($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_NOBODY, 1);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	if(curl_exec($ch)!==FALSE) {
		return true;
	} else {
		return false;
	}
}

/**
 * Download file form an URL
 *
 * @param string $url
 * @param string $path
 * @return bool
 */
function downloadFile($url, $path){
	$out = false;
	$newFile = false;

	$newFileName = $path;
	$file = fopen ($url, 'rb');
	if ($file) {
		$newFile = fopen ($newFileName, 'wb');
		if ($newFile) {
			while(!feof($file)) {
				fwrite($newFile, fread($file, 1024 * 8), 1024 * 8);
			}
			$out = $path;
		}
	}
	if ($file) {
		fclose($file);
	}
	if ($newFile) {
		fclose($newFile);
	}

	return $out;
}

/**
 * Debug an variable
 * @param mixed $variable
 * @param string $info
 */
function d($variable, $info = ''){
	if(!Empty($info)) {
		print '<h2>' . $info . '</h2>';
	}
	print '<pre>';
	print_r($variable);
	print '</pre>';
}

/**
 * Debug & Die
 * @param mixed $variable
 * @param string $info
 */
function dd($variable, $info = ''){
	d($variable, $info);
	exit();
}

function powerComponents($num, $max = 1024){
	$out = [];
	for($i=1;$i<=$max;$i=$i*2){
		if(($num & $i) == $i){
			$out[] = $i;
		}
	}

	return $out;
}

function getClientIP() {
	$ip = $_SERVER["REMOTE_ADDR"];
	if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	return $ip;
}

function ipInfo($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
	global $ipCache;

	$output = NULL;
	if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
		$ip = $_SERVER["REMOTE_ADDR"];
		if ($deep_detect) {
			if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
				$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
	}
	$purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
	$support    = array("country", "countrycode", "state", "region", "city", "location", "address");
	if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
		if (!empty($ipCache[$ip][$purpose])) {
			$output = $ipCache[$ip][$purpose];
		} else {
			$ipdat = @json_decode(file_get_contents("http://api.ipapi.com/" . $ip . "?access_key=" . IPAPI_KEY));
			if (@strlen(trim($ipdat->country_code)) == 2) {
				switch ($purpose) {
					case "location":
						$output = array(
							"city" => @$ipdat->city,
							"state" => @$ipdat->region_name,
							"country" => @$ipdat->country_name,
							"country_code" => @$ipdat->country_code,
							"continent" => @$ipdat->continent_name,
							"continent_code" => @$ipdat->continent_code,
						);
						break;
					case "address":
						$address = array($ipdat->country_name);
						if (@strlen($ipdat->region_name) >= 1)
							$address[] = $ipdat->region_name;
						if (@strlen($ipdat->city) >= 1)
							$address[] = $ipdat->city;
						$output = implode(", ", array_reverse($address));
						break;
					case "city":
						$output = @$ipdat->city;
						break;
					case "state":
					case "region":
						$output = @$ipdat->region_name;
						break;
					case "country":
						$output = @$ipdat->country_name;
						break;
					case "countrycode":
						$output = @$ipdat->country_code;
						break;
				}
			}
			$ipCache[$ip][$purpose] = $output;
		}
	}
	return $output;
}

function deleteDir($dirPath) {
	if (! is_dir($dirPath)) {
		return true;
	}
	if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
		$dirPath .= '/';
	}
	$files = glob($dirPath . '*', GLOB_MARK);
	foreach ($files as $file) {
		if (is_dir($file)) {
			deleteDir($file);
		} else {
			unlink($file);
		}
	}
	rmdir($dirPath);
}

function localizeName($firstName, $lastName, $language){
	if(!$firstName) $firstName = '';
	if(!$lastName) $lastName = '';

	if($GLOBALS['REGIONAL_SETTINGS'][$language]['nameorder'] == 'first-last'){
		$name = $firstName . ' ' . $lastName;
	}else{
		$name = $lastName . ' ' . $firstName;
	}

	return $name;
}

function getNameFromNumber($num) {
	$numeric = ($num - 1) % 26;
	$letter = chr(65 + $numeric);
	$num2 = intval(($num - 1) / 26);
	if ($num2 > 0) {
		return getNameFromNumber($num2) . $letter;
	} else {
		return $letter;
	}
}

function customArrayMerge($array1, $array2){
	foreach($array2 AS $key => $value){
		if(!isset($array1[$key])){
			$array1[$key] = $value;
		}
	}

	return $array1;
}

function formatHour($hour){
	$totalSec = $hour * 60 * 60;
	return date('H:i:s', strtotime(date('Y-m-d 00:00:00')) + $totalSec);
}

/**
 * Get the user's machine ID (from the cookie)
 *
 * @return string
 */
function getMachineId() {
    if (!isset($_COOKIE[COOKIE_MACHINEID])
        || empty($_COOKIE[COOKIE_MACHINEID])) {
        $machineId = uniqid('', true);
        $expires = 0x7fffffff;
        $folder = '/';
        setcookie(COOKIE_MACHINEID, $machineId, $expires, $folder);
    } else {
        $machineId = $_COOKIE[COOKIE_MACHINEID];
    }
    return $machineId;
}

function readfile_chunked($filename, $retbytes = TRUE) {
    $cnt =0;
    $handle = fopen($filename, "rb");
    if ($handle === false) {
        return false;
    }
    while (!feof($handle)) {
        $buffer = fread($handle, CHUNK_SIZE);
        echo $buffer;
        ob_flush();
        flush();
        if ($retbytes) {
            $cnt += strlen($buffer);
        }
    }
    $status = fclose($handle);
    if ($retbytes && $status) {
        return $cnt; // return num. bytes delivered like readfile() does.
    }

    return $status;
}

function isAssociativeArray($array) {
    if(!Empty($array)) {
        if (array_keys($array) !== range(0, count($array) - 1)) {
            return true;
        } else {
            return false;
        }
    }else{
        return false;
    }
}

function decodeCurrencyCode($currency){
    return $GLOBALS['CURRENCIES'][$currency];
}