<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 게시판과 관련된 모든 기능을 제어한다.
 * 
 * @file /modules/board/ModuleBoard.class.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0.161211
 */
class ModuleBoard {
	/**
	 * iModule 및 Module 코어클래스
	 */
	private $IM;
	private $Module;
	
	/**
	 * DB 관련 변수정의
	 *
	 * @private object $DB DB접속객체
	 * @private string[] $table DB 테이블 별칭 및 원 테이블명을 정의하기 위한 변수
	 */
	private $DB;
	private $table;
	
	/**
	 * 언어셋을 정의한다.
	 * 
	 * @private object $lang 현재 사이트주소에서 설정된 언어셋
	 * @private object $oLang package.json 에 의해 정의된 기본 언어셋
	 */
	private $lang = null;
	private $oLang = null;
	
	/**
	 * DB접근을 줄이기 위해 DB에서 불러온 데이터를 저장할 변수를 정의한다.
	 *
	 * @private $members 회원정보
	 * @private $categories 카테고리정보
	 * @private $prefixes 말머리정보
	 * @private $memberPages 회원관련 컨텍스트를 사용하고 있는 사이트메뉴 정보
	 * @private $logged 현재 로그인한 회원정보
	 */
	private $boards = array();
	private $categories = array();
	private $prefixes = array();
	private $posts = array();
	private $ments = array();
	
	/**
	 * 기본 URL (다른 모듈에서 호출되었을 경우에 사용된다.)
	 */
	private $baseUrl = null;
	
	/**
	 * class 선언
	 *
	 * @param iModule $IM iModule 코어클래스
	 * @param Module $Module Module 코어클래스
	 * @see /classes/iModule.class.php
	 * @see /classes/Module.class.php
	 */
	function __construct($IM,$Module) {
		/**
		 * iModule 및 Module 코어 선언
		 */
		$this->IM = $IM;
		$this->Module = $Module;
		
		/**
		 * 모듈에서 사용하는 DB 테이블 별칭 정의
		 * @see 모듈폴더의 package.json 의 databases 참고
		 */
		$this->table = new stdClass();
		$this->table->admin = 'board_admin_table';
		$this->table->board = 'board_table';
		$this->table->category = 'board_category_table';
		$this->table->prefix = 'board_prefix_table';
		$this->table->post = 'board_post_table';
		$this->table->ment = 'board_ment_table';
		$this->table->ment_depth = 'board_ment_depth_table';
		$this->table->attachment = 'board_attachment_table';
		$this->table->history = 'board_history_table';
	}
	
	/**
	 * 모듈 코어 클래스를 반환한다.
	 * 현재 모듈의 각종 설정값이나 모듈의 package.json 설정값을 모듈 코어 클래스를 통해 확인할 수 있다.
	 *
	 * @return Module $Module
	 */
	function getModule() {
		return $this->Module;
	}
	
	/**
	 * 모듈 설치시 정의된 DB코드를 사용하여 모듈에서 사용할 전용 DB클래스를 반환한다.
	 *
	 * @return DB $DB
	 */
	function db() {
		if ($this->DB == null || $this->DB->ping() === false) $this->DB = $this->IM->db($this->getModule()->getInstalled()->database);
		return $this->DB;
	}
	
	/**
	 * 모듈에서 사용중인 DB테이블 별칭을 이용하여 실제 DB테이블 명을 반환한다.
	 *
	 * @param string $table DB테이블 별칭
	 * @return string $table 실제 DB테이블 명
	 */
	function getTable($table) {
		return empty($this->table->$table) == true ? null : $this->table->$table;
	}
	
	/**
	 * URL 을 가져온다.
	 *
	 * @param string $view
	 * @param string $idx
	 */
	function getUrl($view=null,$idx=null) {
		$url = $this->baseUrl ? $this->baseUrl : $this->IM->getUrl(null,null,false);
		if ($view == null || $view == false) return $url;
		$url.= '/'.$view;
		
		if ($idx == null || $idx == false) return $url;
		return $url.'/'.$idx;
	}
	
	/**
	 * view 값을 가져온다.
	 *
	 * @param string $view
	 */
	function getView() {
		return $this->IM->getView($this->baseUrl);
	}
	
	/**
	 * idx 값을 가져온다.
	 *
	 * @param string $idx
	 */
	function getIdx() {
		return $this->IM->getIdx($this->baseUrl);
	}
	
	/**
	 * [코어] 사이트 외부에서 현재 모듈의 API를 호출하였을 경우, API 요청을 처리하기 위한 함수로 API 실행결과를 반환한다.
	 * 소스코드 관리를 편하게 하기 위해 각 요쳥별로 별도의 PHP 파일로 관리한다.
	 *
	 * @param string $api API명
	 * @return object $datas API처리후 반환 데이터 (해당 데이터는 /api/index.php 를 통해 API호출자에게 전달된다.)
	 * @see /api/index.php
	 */
	function getApi($api) {
		$data = new stdClass();
		
		/**
		 * 이벤트를 호출한다.
		 */
		$this->IM->fireEvent('beforeGetApi','board',$api,$values,null);
		
		/**
		 * 모듈의 api 폴더에 $api 에 해당하는 파일이 있을 경우 불러온다.
		 */
		if (is_file($this->getModule()->getPath().'/api/'.$api.'.php') == true) {
			INCLUDE $this->getModule()->getPath().'/api/'.$api.'.php';
		}
		
		return $data;
	}
	
	/**
	 * [코어] 알림메세지를 구성한다.
	 *
	 * @param string $code 알림코드
	 * @param int $fromcode 알림이 발생한 대상의 고유값
	 * @param array $content 알림데이터
	 * @return string $push 알림메세지
	 */
	function getPush($code,$fromcode,$content) {
		
	}
	
	/**
	 * [코어] 포인트내역 메세지를 구성한다.
	 *
	 * @param string $code 포인트코드
	 * @param array $content 포인트데이터
	 * @return string $point 포인트메세지
	 */
	function getPoint($code,$content) {
		
	}
	
	/**
	 * [사이트관리자] 모듈 설정패널을 구성한다.
	 *
	 * @return string $panel 설정패널 HTML
	 *
	function getConfigPanel() {
		/**
		 * 설정패널 PHP에서 iModule 코어클래스와 모듈코어클래스에 접근하기 위한 변수 선언
		 *
		$IM = $this->IM;
		$Module = $this->getModule();
		
		ob_start();
		INCLUDE $this->getModule()->getPath().'/admin/configs.php';
		$panel = ob_get_contents();
		ob_end_clean();
		
		return $panel;
	}*/
	
	/**
	 * [사이트관리자] 모듈 관리자패널 구성한다.
	 *
	 * @return string $panel 관리자패널 HTML
	 */
	function getAdminPanel() {
		/**
		 * 설정패널 PHP에서 iModule 코어클래스와 모듈코어클래스에 접근하기 위한 변수 선언
		 */
		$IM = $this->IM;
		$Module = $this;
		
		ob_start();
		INCLUDE $this->getModule()->getPath().'/admin/index.php';
		$panel = ob_get_contents();
		ob_end_clean();
		
		return $panel;
	}
	
	/**
	 * [사이트관리자] 모듈의 전체 컨텍스트 목록을 반환한다.
	 *
	 * @return object $lists 전체 컨텍스트 목록
	 */
	function getContexts() {
		$lists = $this->db()->select($this->table->board,'bid,title')->get();
		
		for ($i=0,$loop=count($lists);$i<$loop;$i++) {
			$lists[$i] = array('context'=>$lists[$i]->bid,'title'=>$lists[$i]->title);
		}
		
		return $lists;
	}
	
	/**
	 * [사이트관리자] 모듈의 컨텍스트 환경설정을 구성한다.
	 *
	 * @param object $site 설정대상 사이트
	 * @param object $values 설정값
	 * @param string $context 설정대상 컨텍스트명
	 * @return object[] $configs 환경설정
	 */
	function getContextConfigs($site,$values,$context) {
		$configs = array();
		
		$templet = new stdClass();
		$templet->title = $this->IM->getText('text/templet');
		$templet->name = 'templet';
		$templet->type = 'templet';
		$templet->target = 'board';
		$templet->use_default = true;
		$templet->value = $values != null && isset($values->templet) == true ? $values->templet : '#';
		$configs[] = $templet;
		
		$templet = new stdClass();
		$templet->title = '첨부파일 템플릿';
		$templet->name = 'attachment';
		$templet->type = 'templet';
		$templet->target = 'attachment';
		$templet->use_default = true;
		$templet->value = $values != null && isset($values->attachment) == true ? $values->attachment : '#';
		$configs[] = $templet;
		
		$category = new stdClass();
		$category->title = $this->getText('text/category');
		$category->name = 'category';
		$category->type = 'select';
		$category->data = array();
		$category->data[] = array(0,$this->getText('text/category_all'));
		$categorys = $this->db()->select($this->table->category,'idx,title')->where('bid',$context)->orderBy('sort','asc')->get();
		for ($i=0, $loop=count($categorys);$i<$loop;$i++) {
			$category->data[] = array($categorys[$i]->idx,$categorys[$i]->title);
		}
		$category->value = $values != null && isset($values->category) == true ? $values->category : 0;
		$configs[] = $category;
		
		return $configs;
	}
	
	/**
	 * 언어셋파일에 정의된 코드를 이용하여 사이트에 설정된 언어별로 텍스트를 반환한다.
	 * 코드에 해당하는 문자열이 없을 경우 1차적으로 package.json 에 정의된 기본언어셋의 텍스트를 반환하고, 기본언어셋 텍스트도 없을 경우에는 코드를 그대로 반환한다.
	 *
	 * @param string $code 언어코드
	 * @param string $replacement 일치하는 언어코드가 없을 경우 반환될 메세지 (기본값 : null, $code 반환)
	 * @return string $language 실제 언어셋 텍스트
	 */
	function getText($code,$replacement=null) {
		if ($this->lang == null) {
			if (is_file($this->getModule()->getPath().'/languages/'.$this->IM->language.'.json') == true) {
				$this->lang = json_decode(file_get_contents($this->getModule()->getPath().'/languages/'.$this->IM->language.'.json'));
				if ($this->IM->language != $this->getModule()->getPackage()->language && is_file($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json') == true) {
					$this->oLang = json_decode(file_get_contents($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json'));
				}
			} elseif (is_file($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json') == true) {
				$this->lang = json_decode(file_get_contents($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json'));
				$this->oLang = null;
			}
		}
		
		$returnString = null;
		$temp = explode('/',$code);
		
		$string = $this->lang;
		for ($i=0, $loop=count($temp);$i<$loop;$i++) {
			if (isset($string->{$temp[$i]}) == true) {
				$string = $string->{$temp[$i]};
			} else {
				$string = null;
				break;
			}
		}
		
		if ($string != null) {
			$returnString = $string;
		} elseif ($this->oLang != null) {
			if ($string == null && $this->oLang != null) {
				$string = $this->oLang;
				for ($i=0, $loop=count($temp);$i<$loop;$i++) {
					if (isset($string->{$temp[$i]}) == true) {
						$string = $string->{$temp[$i]};
					} else {
						$string = null;
						break;
					}
				}
			}
			
			if ($string != null) $returnString = $string;
		}
		
		/**
		 * 언어셋 텍스트가 없는경우 iModule 코어에서 불러온다.
		 */
		if ($returnString != null) return $returnString;
		elseif (in_array(reset($temp),array('text','button','action')) == true) return $this->IM->getText($code,$replacement);
		else return $replacement == null ? $code : $replacement;
	}
	
	/**
	 * 상황에 맞게 에러코드를 반환한다.
	 *
	 * @param string $code 에러코드
	 * @param object $value(옵션) 에러와 관련된 데이터
	 * @param boolean $isRawData(옵션) RAW 데이터 반환여부
	 * @return string $message 에러 메세지
	 */
	function getErrorText($code,$value=null,$isRawData=false) {
		$message = $this->getText('error/'.$code,$code);
		if ($message == $code) return $this->IM->getErrorText($code,$value,null,$isRawData);
		
		$description = null;
		switch ($code) {
			case 'NOT_ALLOWED_SIGNUP' :
				if ($value != null && is_object($value) == true) {
					$description = $value->title;
				}
				break;
				
			case 'DISABLED_LOGIN' :
				if ($value != null && is_numeric($value) == true) {
					$description = str_replace('{SECOND}',$value,$this->getText('text/remain_time_second'));
				}
				break;
			
			default :
				if (is_object($value) == false && $value) $description = $value;
		}
		
		$error = new stdClass();
		$error->message = $message;
		$error->description = $description;
		$error->type = 'BACK';
		
		if ($isRawData === true) return $error;
		else return $this->IM->getErrorText($error);
	}
	
	/**
	 * 특정 컨텍스트에 대한 제목을 반환한다.
	 *
	 * @param string $context 컨텍스트명
	 * @return string $title 컨텍스트 제목
	 */
	function getContextTitle($context) {
		$board = $this->getBoard($context);
		if ($board == null) return '삭제된 게시판';
		return $board->title.'('.$board->bid.')';
	}
	
	/**
	 * 사이트맵에 나타날 뱃지데이터를 생성한다.
	 *
	 * @param string $context 컨텍스트종류
	 * @param object $configs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
	 * @return object $badge 뱃지데이터 ($badge->count : 뱃지숫자, $badge->latest : 뱃지업데이트 시각(UNIXTIME), $badge->text : 뱃지텍스트)
	 * @todo check count information
	 */
	function getContextBadge($context,$config) {
		/**
		 * null 일 경우 뱃지를 표시하지 않는다.
		 */
		return null;
	}
	
	/**
	 * 템플릿 정보를 가져온다.
	 *
	 * @param string $this->getTemplet($configs) 템플릿명
	 * @return string $package 템플릿 정보
	 */
	function getTemplet($templet=null) {
		$templet = $templet == null ? '#' : $templet;
		
		/**
		 * 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정일 경우
		 */
		if (is_object($templet) == true) {
			$templet_configs = $templet !== null && isset($templet->templet_configs) == true ? $templet->templet_configs : null;
			$templet = $templet !== null && isset($templet->templet) == true ? $templet->templet : '#';
		} else {
			$templet_configs = null;
		}
		
		/**
		 * 템플릿명이 # 이면 모듈 기본설정에 설정된 템플릿을 사용한다.
		 */
		if ($templet == '#') {
			$templet = $this->getModule()->getConfig('templet');
			$templet_configs = $this->getModule()->getConfig('templet_configs');
		}
		
		return $this->getModule()->getTemplet($templet,$templet_configs);
	}
	
	/**
	 * 페이지 컨텍스트를 가져온다.
	 *
	 * @param string $context 컨테이너 종류
	 * @param object $configs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
	 * @return string $html 컨텍스트 HTML
	 */
	function getContext($bid,$configs=null) {
		/**
		 * 모듈 기본 스타일 및 자바스크립트
		 */
		$this->IM->addHeadResource('style',$this->getModule()->getDir().'/styles/style.css');
		$this->IM->addHeadResource('script',$this->getModule()->getDir().'/scripts/script.js');
		
		$values = new stdClass();
		
		if ($configs != null && isset($configs->baseUrl) == true) $this->baseUrl = $configs->baseUrl;
		
		$view = $this->getView() == null ? 'list' : $this->getView();
		
		$board = $this->getBoard($bid);
		if ($board == null) return $this->getTemplet($configs)->getError('NOT_FOUND_PAGE');
		
		if ($configs == null) $configs = new stdClass();
		if (isset($configs->templet) == false) $configs->templet = '#';
		if ($configs->templet == '#') {
			$configs->templet = $board->templet;
			$configs->templet_configs = $board->templet_configs;
		} else {
			$configs->templet_configs = isset($configs->templet_configs) == true ? $configs->templet_configs : null;
		}

		$html = PHP_EOL.'<!-- BOARD MODULE -->'.PHP_EOL.'<div data-role="context" data-type="module" data-module="board" data-base-url="'.($configs == null || isset($configs->baseUrl) == false ? '' : $configs->baseUrl).'" data-bid="'.$bid.'" data-view="'.$view.'">'.PHP_EOL;
		$html.= $this->getHeader($bid,$configs);
		
		switch ($view) {
			case 'list' :
				$html.= $this->getListContext($bid,$configs);
				break;
			
			case 'view' :
				$html.= $this->getViewContext($bid,$configs);
				break;
			
			case 'write' :
				$html.= $this->getWriteContext($bid,$configs);
				break;
		}
		
		$html.= $this->getFooter($bid,$configs);
		
		/**
		 * 컨텍스트 컨테이너를 설정한다.
		 */
		$html.= PHP_EOL.'</div>'.PHP_EOL.'<!--// BOARD MODULE -->'.PHP_EOL;
		
		return $html;
	}
	
	/**
	 * 모듈 외부컨테이너를 가져온다.
	 *
	 * @param string $container 컨테이너명
	 * @return string $html 컨텍스트 HTML
	 */
	function getContainer($container) {
		$html = $this->getContext($container);
		
		$this->IM->addHeadResource('style',$this->getModule()->getDir().'/styles/container.css');
		
		$this->IM->removeTemplet();
		$footer = $this->IM->getFooter();
		$header = $this->IM->getHeader();
		
		return $header.$html.$footer;
	}
	
	/**
	 * 컨텍스트 헤더를 가져온다.
	 *
	 * @param string $bid 게시판 ID
	 * @param object $configs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
	 * @return string $html 컨텍스트 HTML
	 */
	function getHeader($bid,$configs=null) {
		/**
		 * 템플릿파일을 호출한다.
		 */
		return $this->getTemplet($configs)->getHeader(get_defined_vars());
	}
	
	/**
	 * 컨텍스트 푸터를 가져온다.
	 *
	 * @param string $context 컨테이너 종류
	 * @param object $configs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
	 * @return string $html 컨텍스트 HTML
	 */
	function getFooter($context,$configs=null) {
		/**
		 * 템플릿파일을 호출한다.
		 */
		return $this->getTemplet($configs)->getFooter(get_defined_vars());
	}
	
	/**
	 * 에러메세지를 반환한다.
	 *
	 * @param string $code 에러코드 (에러코드는 iModule 코어에 의해 해석된다.)
	 * @param object $value 에러코드에 따른 에러값
	 * @return $html 에러메세지 HTML
	 */
	function getError($code,$value=null) {
		/**
		 * iModule 코어를 통해 에러메세지를 구성한다.
		 */
		$error = $this->getErrorText($code,$value,true);
		return $this->IM->getError($error);
	}
	
	/**
	 * 게시물 목록 컨텍스트를 가져온다.
	 *
	 * @param string $bid 게시판 ID
	 * @param object $configs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
	 * @return string $html 컨텍스트 HTML
	 */
	function getListContext($bid,$configs=null) {
		if ($this->checkPermission($bid,'list') == false) return $this->getTemplet($configs)->getError('FORBIDDEN');
		
		$this->IM->addHeadResource('meta',array('name'=>'robots','content'=>'noidex,follow'));
		
		$board = $this->getBoard($bid);
		
		if ($board->use_category != 'NONE') {
			$categories = $this->db()->select($this->table->category)->where('bid',$bid)->get();
		} else {
			$categories = array();
		}
		
		$idx = $this->getIdx() ? explode('/',$this->getIdx()) : array(1);
		$category = null;
		if (count($idx) == 2) list($category,$p) = $idx;
		elseif (count($idx) == 1) list($p) = $idx;
		
		if ($configs != null && isset($configs->category) == true && $configs->category != 0) {
			$category = $configs->category;
			$categories = array();
		}
		
		if ($configs != null && isset($configs->p) == true) {
			$p = $configs->p;
		}
		
		$limit = $board->post_limit;
		$start = ($p - 1) * $limit;
		
		$sort = Request('sort') ? Request('sort') : 'idx';
		$dir = Request('dir') ? Request('dir') : (in_array($sort,array('idx')) == true ? 'desc' : 'asc');
		
		$notice = $this->db()->select($this->table->post)->where('bid',$bid)->where('is_notice','TRUE')->count();
		
		if ($board->view_notice_count == 'INCLUDE') {
			if ($board->view_notice_page == 'FIRST') {
				if (ceil($notice / $limit) >= $p) {
					$notices = $this->db()->select($this->table->post)->where('bid',$bid)->where('is_notice','TRUE')->orderBy('reg_date','desc')->limit($start,$limit)->get();
					$start = 0;
					$limit = $limit - count($notices);
				} else {
					$notices = array();
					$start = $start - $notice;
				}
				
				$lists = $this->db()->select($this->table->post)->where('bid',$bid)->where('is_notice','FALSE');
			} elseif ($board->view_notice_page == 'ALL') {
				$notices = $this->db()->select($this->table->post)->where('bid',$bid)->where('is_notice','TRUE')->orderBy('reg_date','desc')->limit(0,$limit)->get();
				
				$start = ($p - 1) * ($limit - count($notices));
				$limit = $limit - count($notices);
				
				echo $start.'~'.$limit;
				$lists = $this->db()->select($this->table->post)->where('bid',$bid)->where('is_notice','FALSE');
			}
		} else {
			if ($p == 1 || $board->view_notice_page == 'ALL') {
				$notices = $this->db()->select($this->table->post)->where('bid',$bid)->where('is_notice','TRUE')->orderBy('reg_date','desc')->limit($start,$limit)->get();
			} else {
				$notices = array();
			}
			$lists = $this->db()->select($this->table->post)->where('bid',$bid)->where('is_notice','FALSE');
		}
		
		if ($category != null && $category != 0) $lists->where('category',$category);
		
		$keyword = Request('keyword');
		if ($keyword) $lists = $this->IM->getModule('keyword')->getWhere($lists,array('title','search'),$keyword);
		$total = $lists->copy()->count();
		
		$idx = 0;
		if ($configs != null && isset($configs->idx) == true) {
			$idx = $configs->idx;
		}
		
		$lists = $lists->orderBy($sort,$dir)->limit($start,$limit)->get();
		
		for ($i=0, $loop=count($notices);$i<$loop;$i++) {
			$notices[$i] = $this->getPost($notices[$i]);
			$notices[$i]->category = $notices[$i]->category == 0 ? null : $this->getCategory($notices[$i]->category);
			$notices[$i]->prefix = $notices[$i]->prefix == 0 ? null : $this->getPrefix($notices[$i]->prefix);
			$notices[$i]->link = $this->getUrl('view',($board->use_category == 'NONE' ? $notices[$i]->idx : ($category == null ? '0' : $category).'/'.$notices[$i]->idx)).$this->IM->getQueryString().($notices[$i]->is_secret == true ? '#secret-'.$notices[$i]->idx : '');
		}
		
		$loopnum = $total - $start;
		for ($i=0, $loop=count($lists);$i<$loop;$i++) {
			$lists[$i] = $this->getPost($lists[$i]);
			$lists[$i]->loopnum = $loopnum - $i;
			$lists[$i]->category = $lists[$i]->category == 0 ? null : $this->getCategory($lists[$i]->category);
			$lists[$i]->prefix = $lists[$i]->prefix == 0 ? null : $this->getPrefix($lists[$i]->prefix);
			$lists[$i]->link = $this->getUrl('view',($board->use_category == 'NONE' ? $lists[$i]->idx : ($category == null ? '0' : $category).'/'.$lists[$i]->idx)).$this->IM->getQueryString().($lists[$i]->is_secret == true ? '#secret-'.$lists[$i]->idx : '');
		}
		
		$pagination = $this->getTemplet($configs)->getPagination($p,ceil(($total + $notice)/$board->post_limit),$board->page_limit,$this->getUrl('list',($category == null ? '' : $category.'/').'{PAGE}'),$board->page_type);
		
		$link = new stdClass();
		$link->list = $this->getUrl('list',($category == null ? '' : $category.'/').$p);
		$link->write = $this->getUrl('write',false);
		
		$header = PHP_EOL.'<form id="ModuleBoardListForm">'.PHP_EOL;
		$footer = PHP_EOL.'</form>'.PHP_EOL.'<script>Board.list.init("ModuleBoardListForm");</script>'.PHP_EOL;
		
		/**
		 * 템플릿파일을 호출한다.
		 */
		return $this->getTemplet($configs)->getContext('list',get_defined_vars(),$header,$footer);
	}
	
	/**
	 * 게시물 보기 컨텍스트를 가져온다.
	 *
	 * @param string $bid 게시판 ID
	 * @param object $configs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
	 * @return string $html 컨텍스트 HTML
	 */
	function getViewContext($bid,$configs=null) {
		if ($this->checkPermission($bid,'view') == false) return $this->getTemplet($configs)->getError('FORBIDDEN');
		
		$this->IM->addHeadResource('meta',array('name'=>'robots','content'=>'idx,nofollow'));
		
		$board = $this->getBoard($bid);
		$idx = $this->getIdx() ? explode('/',$this->getIdx()) : array(0);
		$category = null;
		$page = 1;
		
		if ($board->use_category == 'NONE') {
			if (count($idx) == 2) list($idx,$page) = $idx;
			elseif (count($idx) == 1) list($idx) = $idx;
		} else {
			if (count($idx) == 3) list($category,$idx,$page) = $idx;
			elseif (count($idx) == 2) list($category,$idx) = $idx;
			elseif (count($idx) == 1) list($idx) = $idx;
		}
		
		$post = $this->getPost($idx);
		if ($post == null) return $this->getTemplet($configs)->getError('NOT_FOUND_PAGE');
		
		if ($post->is_secret == true && $this->checkPermission($bid,'post_secret') == false) {
			if ($post->midx != 0 && $post->midx != $this->IM->getModule('member')->getLogged()) {
				return $this->getError($this->getErrorText('FORBIDDEN'));
			} elseif ($post->midx == 0) {
				$password = Request('password');
				$mHash = new Hash();
				if ($mHash->password_validate($password,$post->password) == false) {
					$context = $this->getError($this->getErrorText('INCORRENT_PASSWORD'));
					$context.= PHP_EOL.'<script>Board.view.secret('.$idx.');</script>'.PHP_EOL;
					
					return $context;
				}
			}
		}
		
		/**
		 * 조회수 증가
		 * @todo 한번에 하나씩만 올리도록 세션처리
		 */
		$this->db()->update($this->table->post,array('hit'=>$this->db()->inc()))->where('idx',$idx)->execute();
		$post->hit = $post->hit + 1;
		
		$post->prefix = $post->prefix == 0 ? null : $this->getPrefix($post->prefix);
		
		/**
		 * 첨부파일
		 */
		$attachments = $this->db()->select($this->table->attachment)->where('type','POST')->where('parent',$idx)->get();
		for ($i=0, $loop=count($attachments);$i<$loop;$i++) {
			$attachments[$i] = $this->IM->getModule('attachment')->getFileInfo($attachments[$i]->idx);
		}
		
		/**
		 * 댓글 컴포넌트를 불러온다.
		 */
		$ment = $this->getMentComponent($idx,null,$configs);
		
		/**
		 * 현재 게시물이 속한 페이지를 구한다.
		 */
		$sort = Request('sort') ? Request('sort') : 'idx';
		$dir = Request('dir') ? Request('dir') : 'asc';
		$previous = $this->db()->select($this->table->post.' p','p.*')->where('p.bid',$post->bid)->where('p.'.$sort,$post->{$sort},$dir == 'desc' ? '<=' : '>=');
		$previous = $previous->count();
		
		$p = ceil($previous/$board->post_limit);
		
		$link = new stdClass();
		$link->list = $this->getUrl('list',($category == null ? '' : $category.'/').$p);
		$link->write = $this->getUrl('write',false);
		
		$header = PHP_EOL.'<div id="ModuleBoardView" data-idx="'.$idx.'">'.PHP_EOL;
		$footer = PHP_EOL.'</div>'.PHP_EOL.'<script>Board.view.init("ModuleBoardView");</script>';
		
		$configs = $configs == null ? new stdClass() : $configs;
		$configs->idx = $idx;
		$configs->p = $p;
		
		$footer.= $this->getListContext($bid,$configs);
		
		/**
		 * 템플릿파일을 호출한다.
		 */
		return $this->getTemplet($configs)->getContext('view',get_defined_vars(),$header,$footer);
	}
	
	/**
	 * 게시물 작성 컨텍스트를 가져온다.
	 *
	 * @param string $bid 게시판 ID
	 * @param object $configs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
	 * @return string $html 컨텍스트 HTML
	 */
	function getWriteContext($bid,$configs=null) {
		if ($this->checkPermission($bid,'post_write') == false) return $this->getTemplet($configs)->getError('FORBIDDEN');
		
		$this->IM->addHeadResource('meta',array('name'=>'robots','content'=>'noidex,nofollow'));
		
		$board = $this->getBoard($bid);
		$idx = $this->getIdx();
		
		if ($board->use_category != 'NONE') {
			$categories = $this->db()->select($this->table->category)->where('bid',$bid)->orderBy('sort','asc')->get();
		} else {
			$categories = array();
		}
		
		if ($board->use_prefix == 'TRUE') {
			$prefixes = $this->db()->select($this->table->prefix)->where('bid',$bid)->orderBy('sort','asc')->get();
		} else {
			$prefixes = array();
		}
		
		/**
		 * 게시물 수정
		 */
		if ($idx !== null) {
			$post = $this->db()->select($this->table->post)->where('idx',$idx)->getOne();
			
			if ($post == null) {
				header("HTTP/1.1 404 Not Found");
				return $this->getError($this->getLanguage('error/notFound'));
			}
			
			if ($this->checkPermission($bid,'post_modify') == false) {
				if ($post->midx != 0 && $post->midx != $this->IM->getModule('member')->getLogged()) {
					return $this->getError($this->getErrorText('FORBIDDEN'));
				} elseif ($post->midx == 0) {
					$password = Request('password');
					$mHash = new Hash();
					if ($mHash->password_validate($password,$post->password) == false) {
						$context = $this->getError($this->getErrorText('INCORRENT_PASSWORD'));
						$context.= PHP_EOL.'<script>Board.view.modify('.$idx.');</script>'.PHP_EOL;
						
						return $context;
					}
				}
			}
			
			$post->content = $this->IM->getModule('wysiwyg')->decodeContent($post->content,false);
		} else {
			$post = null;
		}
		
		$header = PHP_EOL.'<form id="ModuleBoardWriteForm-'.$bid.'" data-autosave="true">'.PHP_EOL;
		$header.= '<input type="hidden" name="templet" value="'.$this->getTemplet($configs)->getName().'">'.PHP_EOL;
		$header.= '<input type="hidden" name="bid" value="'.$bid.'">'.PHP_EOL;
		if ($post !== null) $header.= '<input type="hidden" name="idx" value="'.$post->idx.'">'.PHP_EOL;
		if ($configs != null && isset($configs->category) == true && $configs->category != 0) {
			$categories = array();
			$header.= '<input type="hidden" name="category" value="'.$configs->category.'">'.PHP_EOL;
		}
		$footer = PHP_EOL.'</form>'.PHP_EOL.'<script>Board.write.init("ModuleBoardWriteForm-'.$bid.'");</script>'.PHP_EOL;
		
		$wysiwyg = $this->IM->getModule('wysiwyg')->setModule('board')->setName('content')->setRequired(true)->setContent($post == null ? '' : $post->content);
		
		if ($board->use_attachment == true) {
			$uploader = $this->IM->getModule('attachment');
			if ($configs == null || isset($configs->attachment) == null || $configs->attachment == '#') {
				$attachment_templet_name = $board->attachment->templet;
				$attachment_templet_configs = $board->attachment->templet_configs;
			} else {
				$attachment_templet_name = $configs->attachment;
				$attachment_templet_configs = isset($configs->attachment_configs) == true ? $configs->attachment_configs : null;
			}
			
			if ($attachment_templet_name != '#') {
				$attachment_templet = new stdClass();
				$attachment_templet->templet = $attachment_templet_name;
				$attachment_templet->templet_configs = $attachment_templet_configs;
			} else {
				$attachment_templet = '#';
			}
			
			$uploader = $uploader->setTemplet($attachment_templet)->setModule('board')->setWysiwyg('content');
			if ($post != null) {
				$uploader->setLoader($this->IM->getProcessUrl('board','getFiles',array('idx'=>Encoder(json_encode(array('type'=>'POST','idx'=>$post->idx))))));
			}
			$uploader = $uploader->get();
		} else {
			$uploader = '';
		}
		$wysiwyg = $wysiwyg->get();
		
		/**
		 * 템플릿파일을 호출한다.
		 */
		return $this->getTemplet($configs)->getContext('write',get_defined_vars(),$header,$footer);
	}
	
	/**
	 * 게시물 댓글 컴포넌트
	 *
	 * @param int $parent 댓글을 달린 게시물 번호
	 * @param int $page 댓글 페이지
	 * @param object $configs 설정값
	 * @return string $html
	 */
	function getMentComponent($parent,$page,$configs) {
		$post = $this->getPost($parent);
		$board = $this->getBoard($post->bid);
		
		if ($this->checkPermission($board->bid,'ment_write') == true) {
			$form = $this->getMentWriteComponent($parent,$configs);
		} else {
			if ($post->ment == 0) return '';
			
			$form = '';
		}
		
		$ment = $this->getMentListComponent($parent,null,$configs);
		$pagination = $this->getMentPagination($parent,null,$configs);
		
		$total = '<span data-role="count">'.$post->ment.'</span>';
		
		$header = PHP_EOL.'<div data-role="ment" data-parent="'.$parent.'">'.PHP_EOL;
		$footer = PHP_EOL.'</div>'.PHP_EOL;
		$footer.= PHP_EOL.'<script>Board.ment.init('.$parent.');</script>'.PHP_EOL;
		
		return $this->getTemplet($configs)->getContext('ment',get_defined_vars(),$header,$footer);
	}
	
	/**
	 * 댓글 목록 컴포넌트
	 *
	 * @param int $parent 댓글을 달린 게시물 번호
	 * @param int $page 댓글 페이지
	 * @param object $configs 설정값
	 * @return string $html
	 */
	function getMentListComponent($parent,$page,$configs) {
		$post = $this->getPost($parent);
		$board = $this->getBoard($post->bid);
		
		$total = $this->db()->select($this->table->ment)->where('parent',$parent)->count();
		$page = is_numeric($page) == false || $page == null || $page > max(1,ceil($total/$board->ment_limit)) ? max(1,ceil($total/$board->ment_limit)) : $page;
		$start = ($page - 1) * $board->ment_limit;
		$lists = $this->db()->select($this->table->ment_depth.' d','d.*,m.*')->join($this->table->ment.' m','d.idx=m.idx','LEFT')->where('d.parent',$parent)->orderBy('head','asc')->orderBy('arrange','asc')->limit($start,$board->ment_limit)->get();
		
		$context = PHP_EOL.'<div data-role="list" data-page="'.$page.'">'.PHP_EOL;
		for ($i=0, $loop=count($lists);$i<$loop;$i++) {
			$context.= $this->getMentItemComponent($lists[$i],$configs);
		}
		
		if (count($lists) == 0) $context.= '<div class="empty">'.$this->getText('ment/empty').'</div>'.PHP_EOL;
		
		$context.= PHP_EOL.'</div>'.PHP_EOL;
		
		return $context;
	}
	
	/**
	 * 댓글 페이징 컴포넌트
	 *
	 * @param int $parent 댓글을 달린 게시물 번호
	 * @param int $page 댓글 페이지
	 * @param object $configs 설정값
	 * @return string $html
	 */
	function getMentPagination($parent,$page,$configs) {
		$post = $this->getPost($parent);
		$board = $this->getBoard($post->bid);
		
		$total = $this->db()->select($this->table->ment)->where('parent',$parent)->count();
		$page = is_numeric($page) == false || $page == null || $page > max(1,ceil($total/$board->ment_limit)) ? max(1,ceil($total/$board->ment_limit)) : $page;
		
		if (ceil($total/$board->ment_limit) <= 1) return '<div data-role="pagination" data-page="1"></div>';
		else return $this->getTemplet($configs)->getPagination($page,ceil($total/$board->ment_limit),$board->ment_limit,'#!{PAGE}',$board->page_type);
	}
	
	/**
	 * 댓글 보기 컴포넌트
	 *
	 * @param object 댓글정보
	 * @param object $configs 설정값
	 * @return string $html
	 */
	function getMentItemComponent($ment,$configs) {
		$board = $this->getBoard($ment->bid);
		
		$ment = $this->getMent($ment);
		
		/*
		if ($this->IM->getModule('member')->isLogged() == true) {
			$vote = $this->db()->select($this->table->history)->where('type','MENT')->where('parent',$ment->idx)->where('action','VOTE')->where('midx',$this->IM->getModule('member')->getLogged())->getOne();
			$voted = $vote == null ? null : $vote->result;
		} else {
			$voted = null;
		}
		*/
		
		$ment->is_visible = true;
		if ($ment->is_secret == true) {
			$permission = $this->checkSecretMentPermission($ment->idx);
			
			if ($permission === 'PASSWORD') {
				$ment->content = '<div data-secret="TRUE" data-password="TRUE">'.$this->getText('ment/secret').'</div>';
				$ment->is_visible = false;
			} elseif ($permission === false) {
				$ment->content = '<div data-secret="TRUE" data-password="FALSE">'.$this->getErrorText('FORBIDDEN_SECRET').'</div>';
				$ment->is_visible = false;
			}
		}
		
		if ($ment->is_visible == true) {
			$attachments = $this->db()->select($this->table->attachment)->where('parent',$ment->idx)->where('type','MENT')->get();
			for ($i=0, $loop=count($attachments);$i<$loop;$i++) {
				$attachments[$i] = $this->IM->getModule('attachment')->getFileInfo($attachments[$i]->idx);
			}
		} else {
			$attachments = array();
		}
		
		$header = PHP_EOL.'<div data-role="item" data-idx="'.$ment->idx.'" data-parent="'.$ment->parent.'" data-depth="'.$ment->depth.'" style="margin-left:'.($ment->depth * 20).'px;">'.PHP_EOL;
		$footer = PHP_EOL.'</div>'.PHP_EOL;
		
		/**
		 * 템플릿파일을 호출한다.
		 */
		return $this->getTemplet($configs)->getContext('ment.item',get_defined_vars(),$header,$footer);
	}
	
	/**
	 * 댓글 작성 컴포넌트
	 *
	 * @param int $parent 댓글을 작성할 게시물 번호
	 * @param object $configs 설정값
	 * @return string $html
	 */
	function getMentWriteComponent($parent,$configs) {
		$post = $this->getPost($parent);
		$board = $this->getBoard($post->bid);
		
		$wysiwyg = $this->IM->getModule('wysiwyg')->setModule('board')->setName('content')->setHeight(100)->setRequired(true);
		
		if ($board->use_attachment == true) {
			$uploader = $this->IM->getModule('attachment');
			if ($configs == null || isset($configs->attachment) == null || $configs->attachment == '#') {
				$attachment_templet_name = $board->attachment->templet;
				$attachment_templet_configs = $board->attachment->templet_configs;
			} else {
				$attachment_templet_name = $configs->attachment;
				$attachment_templet_configs = isset($configs->attachment_configs) == true ? $configs->attachment_configs : null;
			}
			
			if ($attachment_templet_name != '#') {
				$attachment_templet = new stdClass();
				$attachment_templet->templet = $attachment_templet_name;
				$attachment_templet->templet_configs = $attachment_templet_configs;
			} else {
				$attachment_templet = '#';
			}
			
			$uploader = $uploader->setTemplet($attachment_templet)->setModule('board')->setWysiwyg('content')->get();
		} else {
			$uploader = '';
		}
		$wysiwyg = $wysiwyg->get();
		
		$header = PHP_EOL.'<div id="ModuleBoardMentWrite-'.$parent.'">'.PHP_EOL;
		$header.= '<form id="ModuleBoardMentForm-'.$parent.'">'.PHP_EOL;
		$header.= '<input type="hidden" name="idx" value="">'.PHP_EOL;
		$header.= '<input type="hidden" name="parent" value="'.$parent.'">'.PHP_EOL;
		$header.= '<input type="hidden" name="source" value="">'.PHP_EOL;
		$footer = PHP_EOL.'</form>'.PHP_EOL;
		$footer.= '</div>'.PHP_EOL;
		$footer.= '<script>Board.ment.init("ModuleBoardMentForm-'.$parent.'");</script>';
		
		/**
		 * 템플릿파일을 호출한다.
		 */
		return $this->getTemplet($configs)->getContext('ment.write',get_defined_vars(),$header,$footer);
	}
	
	/**
	 * 패스워드 확인 모들을 가져온다.
	 *
	 * @return string $html 모달 HTML
	 */
	function getPasswordModal($type,$idx) {
		$title = '패스워드 확인';
		$content = '<input type="hidden" name="type" value="'.$type.'">';
		$content.= '<input type="hidden" name="idx" value="'.$idx.'">';
		$content.= '<div data-role="message">';
		
		if ($type == 'post_modify') $content.= '게시물을 수정하려면 패스워드를 입력하여 주십시오.';
		if ($type == 'post_secret') $content.= '비밀글을 열람하시려면 패스워드를 입력하여 주십시오.';
		
		if ($type == 'ment_modify') $content.= '댓글을 수정하려면 패스워드를 입력하여 주십시오.';
		if ($type == 'ment_secret') {
			$ment = $this->getMent($idx);
			if ($ment->source == 0) {
				$content.= '비밀댓글을 열람하시려면 댓글 또는 게시물의 패스워드를 입력하여 주십시오.';
			} else {
				$content.= '비밀댓글을 열람하시려면 댓글 또는 부모댓글의 패스워드를 입력하여 주십시오.';
			}
		}
		
		$content.= '</div>';
		$content.= '<div data-role="input"><input type="password" name="password"></div>';
		
		
		$buttons = array();
		
		$button = new stdClass();
		$button->type = 'close';
		$button->text = '취소';
		$buttons[] = $button;
		
		$button = new stdClass();
		$button->type = 'submit';
		$button->text = '확인';
		$buttons[] = $button;
		
		return $this->getTemplet()->getModal($title,$content,true,array(),$buttons);
	}
	
	/**
	 * 삭제모달을 가져온다.
	 *
	 * @param string $type post or ment
	 * @param int $idx 게시물/댓글 고유번호
	 * @return string $html 모달 HTML
	 */
	function getDeleteModal($type,$idx) {
		$title = $type == 'post' ? '게시물 삭제' : '댓글 삭제';
		
		$content = '<input type="hidden" name="type" value="'.$type.'">'.PHP_EOL;
		$content.= '<input type="hidden" name="idx" value="'.$idx.'">'.PHP_EOL;
		
		if ($type == 'post') {
			$post = $this->getPost($idx);
			
			if ($this->checkPermission($post->bid,'post_delete') == false && $post->midx != 0 && $post->midx != $this->IM->getModule('member')->getLogged()) return;
			
			$content.= '<div data-role="message">게시물을 삭제하시겠습니까?</div>';
			
			if ($this->checkPermission($post->bid,'post_delete') == false && $post->midx == 0) {
				$content.= '<div data-role="input" data-default="게시물 등록시 입력한 패스워드를 입력하여 주십시오."><input type="password" name="password"></div>';
			}
		} elseif ($type == 'ment') {
			$ment = $this->getMent($idx);
			
			if ($this->checkPermission($ment->bid,'ment_delete') == false && $ment->midx != 0 && $ment->midx != $this->IM->getModule('member')->getLogged()) return;
			
			$content.= '<div data-role="message">댓글을 삭제하시겠습니까?</div>';
			if ($this->checkPermission($ment->bid,'ment_delete') == false && $ment->midx == 0) {
				$content.= '<div data-role="input" data-default="댓글 등록시 입력한 패스워드를 입력하여 주십시오."><input type="password" name="password"></div>';
			}
		}
		
		$buttons = array();
		
		$button = new stdClass();
		$button->type = 'submit';
		$button->text = '삭제하기';
		$button->class = 'danger';
		$buttons[] = $button;
		
		$button = new stdClass();
		$button->type = 'close';
		$button->text = '취소';
		$buttons[] = $button;
		
		return $this->getTemplet()->getModal($title,$content,true,array(),$buttons);
	}
	
	/**
	 * 게시판정보를 가져온다.
	 *
	 * @param string $bid
	 * @return object $board
	 */
	function getBoard($bid) {
		if (isset($this->boards[$bid]) == true) return $this->boards[$bid];
		$board = $this->db()->select($this->table->board)->where('bid',$bid)->getOne();
		if ($board == null) {
			$this->boards[$bid] = null;
		} else {
			$board->templet_configs = json_decode($board->templet_configs);
			
			$attachment = json_decode($board->attachment);
			unset($board->attachment);
			$board->use_attachment = $attachment->attachment;
			if ($board->use_attachment == true) {
				$board->attachment = new stdClass();
				$board->attachment->templet = $attachment->templet;
				$board->attachment->templet_configs = $attachment->templet_configs;
			}
			
			$board->allow_secret = $board->allow_secret == 'TRUE';
			$board->allow_anonymity = $board->allow_anonymity == 'TRUE';
			
			$this->boards[$bid] = $board;
		}
		
		return $this->boards[$bid];
	}
	
	/**
	 * 게시물정보를 가져온다.
	 *
	 * @param int $idx 게시물고유번호
	 * @param int $is_link 게시물 링크를 구할지 여부 (기본값 : false)
	 * @return object $post
	 */
	function getPost($idx,$is_link=false) {
		if (is_null($idx) == true) return null;
		
		if (is_numeric($idx) == true) {
			if (isset($this->posts[$idx]) == true) return $this->posts[$idx];
			else return $this->getPost($this->db()->select($this->table->post)->where('idx',$idx)->getOne(),$is_link);
		} else {
			$post = $idx;
			if (isset($post->is_rendered) === true && $post->is_rendered === true) return $post;
			
			$post->member = $this->IM->getModule('member')->getMember($post->midx);
			$post->name = $this->IM->getModule('member')->getMemberName($post->midx,$post->name,true);
			$post->nickname = $this->IM->getModule('member')->getMemberNickname($post->midx,$post->name,true);
			$post->photo = $this->IM->getModule('member')->getMemberPhoto($post->midx);
			
			if ($is_link == true) {
				$page = $this->IM->getContextUrl('board',$post->bid,array(),array('category'=>$post->category),true);
				$post->link = $page == null ? '#' : $this->IM->getUrl($page->menu,$page->page,'view',$post->idx);
			}
			
			$post->image = $post->image > 0 ? $this->IM->getModule('attachment')->getFileInfo($post->image) : null;
			
			$post->content = $this->IM->getModule('wysiwyg')->decodeContent($post->content);
			
			$post->is_secret = $post->is_secret == 'TRUE';
			$post->is_anonymity = $post->is_anonymity == 'TRUE';
			$post->is_notice = $post->is_notice == 'TRUE';
			
			if ($post->is_anonymity == true) {
				$post->name = $post->nickname = '<span data-module="member" data-role="name">익명-'.strtoupper(substr(base_convert(ip2long($post->ip),10,32),0,6)).'</span>';
				$post->photo = '<i data-module="member" data-role="photo" style="background-image:url('.$this->getModule()->getDir().'/images/icon_'.(ip2long($post->ip) % 2 == 0 ? 'man' : 'woman').'.png);"></i>';
			}
			
			$post->is_rendered = true;
			
			$this->posts[$post->idx] = $post;
			return $this->posts[$post->idx];
		}
	}
	
	/**
	 * 댓글정보를 가져온다.
	 *
	 * @param int $idx 댓글 고유번호
	 * @param int $is_link 게시물 링크를 구할지 여부 (기본값 : false)
	 * @return object $ment
	 */
	function getMent($idx,$is_link=false) {
		if (is_null($idx) == true) return null;
		
		if (is_numeric($idx) == true) {
			if (isset($this->ments[$idx]) == true) return $this->ments[$idx];
			else return $this->getMent($this->db()->select($this->table->ment_depth.' d','d.*,m.*')->join($this->table->ment.' m','d.idx=m.idx','LEFT')->where('d.idx',$idx)->getOne());
		} else {
			$ment = $idx;
			if (isset($ment->is_rendered) === true && $ment->is_rendered === true) return $ment;
			
			$ment->member = $this->IM->getModule('member')->getMember($ment->midx);
			$ment->name = $this->IM->getModule('member')->getMemberName($ment->midx,$ment->name,true);
			$ment->nickname = $this->IM->getModule('member')->getMemberNickname($ment->midx,$ment->name,true);
			$ment->photo = $this->IM->getModule('member')->getMemberPhoto($ment->midx);
			
			if ($is_link == true) {
//				$page = $this->IM->getContextUrl('board',$ment->bid,array(),array('category'=>$post->category),true);
//				$post->link = $page == null ? '#' : $this->IM->getUrl($page->menu,$page->page,'view',$post->idx);
			}
			
//			$post->image = $post->image > 0 ? $this->IM->getModule('attachment')->getFileInfo($post->image) : null;
			
			$ment->content = $this->IM->getModule('wysiwyg')->decodeContent($ment->content);
			
			$ment->is_secret = $ment->is_secret == 'TRUE';
			$ment->is_anonymity = $ment->is_anonymity == 'TRUE';
			$ment->is_delete = $ment->is_delete == 'TRUE';
			$ment->is_rendered = true;
			
			if ($ment->is_anonymity == true) {
				$ment->name = $ment->nickname = '<span data-module="member" data-role="name">익명-'.strtoupper(substr(base_convert(ip2long($ment->ip),10,32),0,6)).'</span>';
				$ment->photo = '<i data-module="member" data-role="photo" style="background-image:url('.$this->getModule()->getDir().'/images/icon_'.(ip2long($ment->ip) % 2 == 0 ? 'man' : 'woman').'.png);"></i>';
			}
			
			$this->ments[$ment->idx] = $ment;
			return $this->ments[$ment->idx];
		}
	}
	
	/**
	 * 댓글이 위치한 페이지번호를 가져온다.
	 *
	 * @param int $idx 댓글번호
	 * @return int $page 페이지번호
	 */
	function getMentPage($idx) {
		$ment = $this->getMent($idx);
		if ($ment == null) return null;
		
		$board = $this->getBoard($ment->bid);
		$position = $this->db()->select($this->table->ment_depth)->where('parent',$ment->parent)->where('head',$ment->head,'<=')->where('arrange',$ment->arrange,'<=')->count();
		$page = ceil($position/$board->ment_limit);
		
		return $page;
	}
	
	/**
	 * 카테고리정보를 가져온다.
	 *
	 * @param int $idx 카테고리고유번호
	 * @return object $category
	 */
	function getCategory($idx) {
		if (isset($this->categories[$idx]) == true) return $this->categories[$idx];
		$this->categories[$idx] = $this->db()->select($this->table->category)->where('idx',$idx)->getOne();
		return $this->categories[$idx];
	}
	
	/**
	 * 말머리정보를 가져온다.
	 *
	 * @param int $idx 말머리고유번호
	 * @return object $prefix
	 */
	function getPrefix($idx) {
		if (isset($this->prefixes[$idx]) == true) return $this->prefixes[$idx];
		$this->prefixes[$idx] = $this->db()->select($this->table->prefix)->where('idx',$idx)->getOne();
		return $this->prefixes[$idx];
	}
	
	/**
	 * 권한을 확인한다.
	 *
	 * @param string $bid 게시판 ID
	 * @param string $type 확인할 권한코드
	 * @return boolean $hasPermssion
	 */
	function checkPermission($bid,$type) {
		if ($this->IM->getModule('member')->isAdmin() == true || $this->isAdmin(null,$bid) == true) return true;
		
		$board = $this->getBoard($bid);
		$permission = json_decode($board->permission);
		
		if (isset($permission->{$type}) == false) return false;
		return $this->IM->parsePermissionString($permission->{$type});
	}
	
	/**
	 * 비밀댓글 열람권한을 확인한다.
	 *
	 * @param string $idx 댓글고유번호
	 * @return boolean $hasPermssion
	 */
	function checkSecretMentPermission($idx) {
		$ment = $this->getMent($idx);
		if ($ment == null) return false;
		
		if ($ment->is_secret == false) return true;
		if ($this->checkPermission($ment->bid,'ment_secret') == true) return true;
		if ($ment->midx != 0 && $ment->midx == $this->IM->getModule('member')->getLogged()) return true;
		
		$permittedSecretMents = Request('ModuleBoardPermittedSecretMents','session') ? Request('ModuleBoardPermittedSecretMents','session') : array();
		if (in_array($ment->idx,$permittedSecretMents) == true) return true;
		
		if ($ment->source == 0) {
			$post = $this->getPost($ment->parent);
			if ($post->midx != 0 && $post->midx == $this->IM->getModule('member')->getLogged()) return true;
			if ($post->midx == 0 || $ment->midx == 0) return 'PASSWORD';
			else return false;
		} else {
			$parent = $this->getMent($ment->source);
			if ($parent->midx != 0 && $parent->midx == $this->IM->getModule('member')->getLogged()) return true;
			if ($parent->midx == 0 || $ment->midx == 0) return 'PASSWORD';
			else return false;
		}
		
		return false;
	}
	
	/**
	 * 게시판 정보를 업데이트한다.
	 *
	 * @param string $bid 게시판 ID
	 */
	function updateBoard($bid) {
		$status = $this->db()->select($this->table->post,'COUNT(*) as total, MAX(reg_date) as latest, SUM(ment) as ment, MAX(latest_ment) as latest_ment')->where('bid',$bid)->getOne();
		$this->db()->update($this->table->board,array('post'=>$status->total,'latest_post'=>($status->latest ? $status->latest : 0),'ment'=>$status->ment,'latest_ment'=>($status->latest_ment ? $status->latest_ment : 0)))->where('bid',$bid)->execute();
	}
	
	/**
	 * 게시물 정보를 업데이트한다.
	 *
	 * @param int $idx 게시물고유번호
	 */
	function updatePost($idx) {
		$status = $this->db()->select($this->table->ment,'COUNT(*) as total, MAX(reg_date) as latest')->where('parent',$idx)->where('is_delete','FALSE')->getOne();
		$this->db()->update($this->table->post,array('ment'=>$status->total,'latest_ment'=>($status->latest ? $status->latest : 0)))->where('idx',$idx)->execute();
	}
	
	/**
	 * 카테고리 정보를 업데이트한다.
	 *
	 * @param int $category 카테고리고유번호
	 */
	function updateCategory($category) {
		if ($category == 0) return;
		
		$status = $this->db()->select($this->table->post,'COUNT(*) as total, MAX(reg_date) as latest')->where('category',$category)->getOne();
		$this->db()->update($this->table->category,array('post'=>$status->total,'latest_post'=>($status->latest ? $status->latest : 0)))->where('idx',$category)->execute();
	}
	
	/**
	 * 말머리 정보를 업데이트한다.
	 *
	 * @param int $prefix 말머리고유번호
	 */
	function updatePrefix($prefix) {
		if ($prefix == 0) return;
		
		$status = $this->db()->select($this->table->post,'COUNT(*) as total, MAX(reg_date) as latest')->where('prefix',$prefix)->getOne();
		$this->db()->update($this->table->prefix,array('post'=>$status->total,'latest_post'=>($status->latest ? $status->latest : 0)))->where('idx',$prefix)->execute();
	}
	
	/**
	 * 게시물을 삭제한다.
	 *
	 * @param int $idx 게시물고유번호
	 */
	function deletePost($idx) {
		$post = $this->getPost($idx);
		if ($post == null) return false;
		
		/**
		 * 게시물에 첨부된 첨부파일을 삭제한다.
		 */
		$attachments = $this->db()->select($this->table->attachment)->where('type','POST')->where('parent',$idx)->get();
		for ($i=0, $loop=count($attachments);$i<$loop;$i++) {
			$this->IM->getModule('attachment')->fileDelete($attachments[$i]->idx);
		}
		
		/**
		 * 게시물에 작성된 댓글을 삭제한다.
		 */
		$ments = $this->db()->select($this->table->ment)->where('parent',$idx)->orderBy('reg_date','desc')->get();
		for ($i=0, $loop=count($ments);$i<$loop;$i++) {
			$this->deleteMent($ments[$i]->idx);
		}
		
		/**
		 * 게시물을 삭제한다.
		 */
		$this->db()->delete($this->table->post)->where('idx',$idx)->execute();
		if ($post->category != 0) $this->updateCategory($post->category);
		$this->updateBoard($post->bid);
		
		return true;
	}
	
	/**
	 * 댓글을 삭제한다.
	 *
	 * @param int $idx 댓글고유번호
	 * @return boolean $success
	 */
	function deleteMent($idx) {
		$ment = $this->getMent($idx);
		if ($ment == null) return false;
		
		/**
		 * 게시물에 첨부된 첨부파일을 삭제한다.
		 */
		$attachments = $this->db()->select($this->table->attachment)->where('type','MENT')->where('parent',$idx)->get();
		for ($i=0, $loop=count($attachments);$i<$loop;$i++) {
			$this->IM->getModule('attachment')->fileDelete($attachments[$i]->idx);
		}
		
		if ($this->hasChildrenMent($idx) == true) {
			$this->db()->update($this->table->ment,array('is_delete'=>'TRUE'))->where('idx',$idx)->execute();
		} else {
			$this->db()->delete($this->table->ment)->where('idx',$idx)->execute();
			$this->db()->delete($this->table->ment_depth)->where('idx',$idx)->execute();
			
			while ($ment->source > 0) {
				$ment = $this->getMent($ment->source);
				if ($ment->is_delete == true && $this->hasChildrenMent($ment->idx) == false) {
					$this->db()->delete($this->table->ment)->where('idx',$ment->idx)->execute();
					$this->db()->delete($this->table->ment_depth)->where('idx',$ment->idx)->execute();
				}
			}
		}
		
		$this->updatePost($ment->parent);
		
		return true;
	}
	
	/**
	 * 삭제되지 않은 자식 댓글이 있는지 확인한다.
	 *
	 * @param int $parent 부모댓글고유번호
	 * @return boolean $hasChildren
	 */
	function hasChildrenMent($parent) {
		$children = $this->db()->select($this->table->ment_depth.' d','m.idx, m.is_delete')->join($this->table->ment.' m','d.idx=m.idx','LEFT')->where('d.source',$parent)->get();
		
		foreach ($children as $ment) {
			if ($ment->is_delete == 'FALSE') return true;
			elseif ($this->hasChildrenMent($ment->idx) == true) return true;
		}
		
		$parent = $this->getMent($parent);
		if ($parent->is_delete == true) {
			foreach ($children as $ment) {
				$this->db()->delete($this->table->ment)->where('idx',$ment->idx)->execute();
				$this->db()->delete($this->table->ment_depth)->where('idx',$ment->idx)->execute();
			}
		}
		
		return false;
	}
	
	/**
	 * 현재 모듈에서 처리해야하는 요청이 들어왔을 경우 처리하여 결과를 반환한다.
	 * 소스코드 관리를 편하게 하기 위해 각 요쳥별로 별도의 PHP 파일로 관리한다.
	 * 작업코드가 '@' 로 시작할 경우 사이트관리자를 위한 작업으로 최고관리자 권한이 필요하다.
	 *
	 * @param string $action 작업코드
	 * @return object $results 수행결과
	 * @see /process/index.php
	 */
	function doProcess($action) {
		$results = new stdClass();
		
		/**
		 * 모듈의 process 폴더에 $action 에 해당하는 파일이 있을 경우 불러온다.
		 */
		if (is_file($this->getModule()->getPath().'/process/'.$action.'.php') == true) {
			INCLUDE $this->getModule()->getPath().'/process/'.$action.'.php';
		}
		
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('afterDoProcess','member',$action,$values,$results);
		
		return $results;
	}
	
	/**
	 * 첨부파일을 동기화한다.
	 *
	 * @param string $action 동기화작업
	 * @param int $idx 파일 고유번호
	 */
	function syncAttachment($action,$idx) {
		/**
		 * 첨부파일 삭제
		 */
		if ($action == 'delete') {
			$this->db()->delete($this->table->attachment)->where('idx',$idx)->execute();
		}
	}
	
	/**
	 * 회원모듈과 동기화한다.
	 *
	 * @param string $action 동기화작업
	 * @param any[] $data 정보
	 */
	function syncMember($action,$data) {
		if ($action == 'point_history') {
			switch ($data->code) {
				case 'post' :
					$idx = $data->content->idx;
					$post = $this->getPost($idx,true);
					
					return '<a href="'.$post->link.'" target="_blank">['.$post->title.']</a> 게시물 작성';
			}
			
			return json_encode($data);
		}
	}
	
	/**
	 * 모듈관리자인지 확인한다.
	 *
	 * @param int $midx 회원고유번호 (없을 경우 현재 로그인한 사용자)
	 * @return boolean $isAdmin
	 */
	function isAdmin($midx=null,$bid=null) {
		$midx = $midx == null ? $this->IM->getModule('member')->getLogged() : $midx;
		if ($this->IM->getModule('member')->isAdmin($midx) == true) return true;
		
		$check = $this->db()->select($this->table->admin)->where('midx',$midx)->getOne();
		if ($check == null) return false;
		if ($check->bid == '*') return true;
		
		$bids = explode(',',$check->bid);
		if ($bid != null && in_array($bid,$bids) === false) return false;
		
		return true;
	}
}
?>