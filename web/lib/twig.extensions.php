<?php
namespace Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension {

	public function getFunctions(){
		global $framework;

		return [
			new TwigFunction('_', 		     [$framework->translate, '_']),
			new TwigFunction('_date', 	     [$framework->lib, 'formatDate']),
			new TwigFunction('_price', 	     [$framework->lib, 'formatPrice']),
			new TwigFunction('_time', 	     [$framework->lib, 'formatTime']),
			new TwigFunction('_dow', 	     [$framework->lib, 'formatDayOfWeek']),
            new TwigFunction('getPageName',  [$framework, 'getPageName']),
            new TwigFunction('_unit', 	     [$this, 'formatUnit']),
			new TwigFunction('_d', 		     [$this, 'd']),
			new TwigFunction('_bool', 	     [$this, 'bool']),
			new TwigFunction('_null', 	     [$this, 'null']),
			new TwigFunction('_json', 	     [$this, 'json']),
			new TwigFunction('_empty', 	     [$this, 'onEmpty']),
			new TwigFunction('formatName', 	 [$this, 'formatName']),
			new TwigFunction('userRole', 	 [$this, 'userRole']),
			new TwigFunction('_color', 	     [$this, 'getUniqueColor']),
            new TwigFunction('projectState', [$this, 'projectState']),
            new TwigFunction('quoteState', 	 [$this, 'quoteState']),
            new TwigFunction('quoteType', 	 [$this, 'quoteType']),
            new TwigFunction('formatBytes',  [$this, 'formatBytes']),
            new TwigFunction('fileTypeIcon', [$this, 'fileTypeIcon']),
            new TwigFunction('extractArray', [$this, 'extractArray'], ['needs_context' => true]),
            new TwigFunction('valueHelper',  [$this, 'valueHelper']),
            new TwigFunction('orderState',   [$this, 'orderState']),
		];
	}

	public function d($array){
		d($array, false);
	}

	public function bool($val){
		if($val){
			$out = 'true';
		}else{
			$out = 'false';
		}
		return $out;
	}

	public function null($val){
		if(!$val){
			$val = 'null';
		}
		return $val;
	}

	public function json($val, $returnNull = false){
		if($val){
			$val = json_encode($val);
		}else {
			if($returnNull) {
				$val = 'null';
			}else {
				$val = '[]';
			}
		}
		return $val;
	}

	public function onEmpty($val, $default, $returnAsString = false){
		if($val === ''){
			$val = $default;
		}else{
			if($returnAsString){
				$val = "'" . $val . "'";
			}
		}
		return $val;
	}

	public function formatName($firstName, $lastName){
		global $framework;
		return localizeName($firstName, $lastName, $framework->language);
	}

	public function userRole($role){
		global $framework;

        $color = 'info';
        $label = '-';

        foreach($GLOBALS['USER_ROLES'] AS $grup => $roles){
            foreach($roles AS $rl => $value){
                if($rl === $role) {
                    $color = $value['color'];
                    $label = $value['label'];
                    break 2;
                }
            }
        }

		return '<span class="badge badge-sm badge-' . $color . '">' . $framework->translate->getTranslation($label) . '</span>';
	}

	public function getUniqueColor($string, $rand = true){
        static $result;
        static $pointer = 0;
        static $used = [];

        $string = strtolower(trim($string));
        $colorArray = [
            'blue',
            'indigo',
            'purple',
            'pink',
            'red',
            'orange',
            'yellow',
            'green',
            'teal',
            'cyan',
            'facebook',
            'twitter',
            'lastfm',
            'pinterest',
            'linkedin',
            'medium',
            'skype',
            'android',
            'spotify',
            'amazon',
        ];

        if(!isset($result[$string])){
            if(!$rand) {
                $result[$string] = $colorArray[$pointer++];
            }else{
                $colorNum = count($colorArray) - 1;
                if(count($used) < $colorNum) {
                    do {
                        $pointer = mt_rand(0, $colorNum);
                        if (!in_array($pointer, $used)) {
                            $used[] = $pointer;
                            break;
                        }
                    } while (count($used) <= $colorNum);
                }else{
                    $pointer = 0;
                }

                $result[$string] = $colorArray[$pointer];
            }
        }

        return $result[$string];
    }

    public function projectState($state){
        global $framework;
        return '<span class="badge badge-sm badge-' . $GLOBALS['PROJECT_STATES'][$state]['color'] . '">' . $framework->translate->getTranslation($GLOBALS['PROJECT_STATES'][$state]['label']) . '</span>';
    }

    public function quoteState($state){
        global $framework;
        return '<span class="badge badge-sm badge-' . $GLOBALS['QUOTE_STATES'][$state]['color'] . '">' . $framework->translate->getTranslation($GLOBALS['QUOTE_STATES'][$state]['label']) . '</span>';
    }

    public function quoteType($type){
        global $framework;
        return '<span class="badge badge-sm badge-' . $GLOBALS['QUOTE_TYPES'][$type]['color'] . '">' . $framework->translate->getTranslation($GLOBALS['QUOTE_TYPES'][$type]['label']) . '</span>';
    }

    public function fileTypeIcon($mimeType){
        switch ($mimeType){
            case 'application/excel':
                $icon = 'file-excel';
                break;
            case 'application/msword':
                $icon = 'file-word';
                break;
            case 'application/pdf':
                $icon = 'file-pdf';
                break;
            case 'application/png':
            case 'application/jpeg':
                $icon = 'file-image';
                break;
            case 'video/avi':
            case 'video/mov':
            case 'video/wmv':
            case 'video/mp4':
                $icon = 'file-video';
                break;

            case 'audio/mp3':
                $icon = 'file-audio';
                break;
            default:
                $icon = 'file';
                break;
        }

        return '<i class="fa fa-' . $icon . ' fa-fw mr-2"></i>';
    }

    public function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function formatUnit($unit){
        return preg_replace("/(\d)/i", "<sup>$1</sup>", $unit);
    }

    public function extractArray(&$context, $array){
        foreach($array as $k => $v) $context[$k] = $v;
    }

    public function valueHelper($values, $id, $name){
	    if(isset($values[$name])) {
            $out = $values[$name];
        }elseif(isset($values[$id])){
            $out = $values[$id];
        }else{
	        $key = explode('][', $name);
	        $out = $values[$key[0]][$key[1]];
        }

        return $out;
    }

    public function orderState($type){
        global $framework;

        return '<span class="badge badge-sm badge-' . str_replace('bg-', '', $GLOBALS['ORDER_STATUSES'][$type]['class']) . '">' . $framework->translate->getTranslation($GLOBALS['ORDER_STATUSES'][$type]['name']) . '</span>';
    }

}
