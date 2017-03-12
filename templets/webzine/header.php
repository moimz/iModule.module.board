<?php
if (defined('__IM__') == false) exit;

$IM->loadWebFont('XEIcon');
?>
<style>
div[data-module=board] ul[data-role=webzine] > li {width:<?php echo sprintf('%.010f',100 / $Templet->getConfig('columns')); ?>%;}
div[data-module=board] ul[data-role=webzine] > li > div {padding-bottom:<?php echo $Templet->getConfig("height"); ?>%;}
</style>