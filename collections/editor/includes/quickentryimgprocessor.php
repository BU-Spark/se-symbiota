<?php
if($LANG_TAG != 'en' && file_exists($SERVER_ROOT.'/content/lang/collections/editor/includes/imgprocessor.'.$LANG_TAG.'.php')) include_once($SERVER_ROOT.'/content/lang/collections/editor/includes/imgprocessor.'.$LANG_TAG.'.php');
else include_once($SERVER_ROOT.'/content/lang/collections/editor/includes/imgprocessor.en.php');
?>
	
<script src="../../js/symb/collections.editor.imgtools.js?ver=4" type="text/javascript"></script>
<link rel="stylesheet" href="../../css/symbiota/quickentry.css" type="text/css">
<style>
	.ocr-box{ padding: 5px; float:left; }
	.ocr-box button{ margin: 5px; }
</style>
<div id="labelProcDiv" style="width:100%;height:425px;position:relative;">
	<fieldset id="labelProcFieldset" style="background-color:#F2F2F3;">
		<div id="labelHeaderDiv" style="margin-top:0px;height:15px;position:relative">
			<div style="float:left;margin-top:3px;margin-right:15px"><a id="zoomInfoDiv" href="#"><?php echo $LANG['ZOOM']; ?></a></div>
			<div id="zoomInfoDialog" style="background-color:white;">
				<?php echo $LANG['ZOOM_DIRECTIONS']; ?>
			</div>
			<div style="float:left;margin-right:15px">
				<div id="draggableImgDiv" style="float:left" title="<?php echo $LANG['MAKE_DRAGGABLE']; ?>"><a href="#" onclick="draggableImgPanel()"><img src="../../images/draggable.png" style="width:15px" /></a></div>
				<div id="anchorImgDiv" style="float:left;margin-left:10px;display:none" title="<?php echo $LANG['ANCHOR_IMG']; ?>"><a href="#" onclick="anchorImgPanel()"><img src="../../images/anchor.png" style="width:15px" /></a></div>
			</div>
			<div style="float:left;;padding-right:10px;margin:2px 20px 0px 0px;"><?php echo $LANG['ROTATE']; ?>: <a href="#" onclick="rotateImage(-90, <?php echo $currentImageId; ?>); return false;">&nbsp;L&nbsp;</a> &lt;&gt; <a href="#" onclick="rotateImage(90, <?php echo $currentImageId; ?>); return false;">&nbsp;R&nbsp;</a></div>
		</div>
		<div id="labelprocessingdiv" style="clear:both;">
			<div id="labeldiv-<?php echo $currentImageId; ?>">
				<div>
					<?php 
					$currentImageUrl = isset($imgUrlCollection[$currentImageId]) ? $imgUrlCollection[$currentImageId] : '';
					if (empty($currentImageUrl) && !empty($imgArr)) {
						foreach ($imgArr as $img) {
							if ($img['imgid'] == $currentImageId) {
								$currentImageUrl = $img['web'];
								break;
							}
						}
					}
					?>
					<img id="activeimg-<?php echo $currentImageId; ?>" src="<?php echo htmlspecialchars($currentImageUrl); ?>" style="height:400px;" onload="initImageTool('activeimg-<?php echo $currentImageId; ?>')" />
				</div>
				<div style="width:100%; clear:both;">
					<div style="float:right; margin-right:20px; font-weight:bold;">
						<span id="current-image-index" style="display:none;"><?php echo $currentImageId; ?></span>
						<?php 
						$currentIndex = 1;
						if (!empty($imgArr)) {
							foreach ($imgArr as $index => $img) {
								if ($img['imgid'] == $currentImageId) {
									$currentIndex = $index;
									break;
								}
							}
						}
						?>
						<span id="image-count">Image <?php echo $currentIndex; ?> of <?php echo count($imgUrlCollection); ?></span>
						<?php if(count($imgUrlCollection) > 1): ?>
							<input type="hidden" id="image-collection-input" value='<?php echo json_encode($imgUrlCollection); ?>'>
							<a href="#" onclick="return nextProcessingImage();">>&gt;</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
</div>