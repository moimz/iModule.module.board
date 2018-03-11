<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 게시판 웹진템플릿 - 게시물 작성 후 프로세스 처리
 * 
 * @file /modules/board/templets/webzine/process/savePost.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 3. 11.
 */
if (defined('__IM__') == false) exit;

if ($results->success == true) {
	/**
	 * 게시물 작성이 완료되면, 편집한 커버이미지를 서버에 저장하고, 게시물 정보를 업데이트한다.
	 */
	$cover = Request('cover');
	$post = $this->getPost($idx);
	
	if ($cover != null && preg_match('/^data:image\/(.*?);base64,(.*?)$/',$cover,$match) == true) {
		$bytes = base64_decode($match[2]);
		$path = $this->IM->getModule('attachment')->getTempPath(true).'/'.md5($bytes).'.'.base_convert(microtime(true)*10000,10,32).'.temp';
		file_put_contents($path,$bytes);
		
		$file = $post->field1 && is_numeric($post->field1) == true ? $this->IM->getModule('attachment')->getFileInfo($post->field1) : null;
		if ($file != null && $file->module == 'board' && $file->target == 'cover') {
			$fileIdx = $file->idx;
			$this->IM->getModule('attachment')->fileReplace($fileIdx,'cover.'.$match[1],$path,true);
		} else {
			$fileIdx = $this->IM->getModule('attachment')->fileSave('cover.'.$match[1],$path,'board','cover','PUBLISHED',true);
		}
		
		$this->db()->update($this->table->post,array('image'=>$fileIdx))->where('idx',$idx)->execute();
	} else {
		$file = $post->field1 && is_numeric($post->field1) == true ? $this->IM->getModule('attachment')->getFileInfo($post->field1) : null;
		if ($file != null && $file->module == 'board' && $file->target == 'cover') {
			$this->IM->getModule('attachment')->fileDelete($file->idx);
		}
		$this->db()->update($this->table->post,array('image'=>'0'))->where('idx',$idx)->execute();
	}
}
?>