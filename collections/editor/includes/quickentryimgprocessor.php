<?php
if($LANG_TAG != 'en' && file_exists($SERVER_ROOT.'/content/lang/collections/editor/includes/imgprocessor.'.$LANG_TAG.'.php')) include_once($SERVER_ROOT.'/content/lang/collections/editor/includes/imgprocessor.'.$LANG_TAG.'.php');
else include_once($SERVER_ROOT.'/content/lang/collections/editor/includes/imgprocessor.en.php');
?>
	
<script src="../../js/symb/collections.editor.imgtools.js?ver=3" type="text/javascript"></script>
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
			<div style="float:left;;padding-right:10px;margin:2px 20px 0px 0px;"><?php echo $LANG['ROTATE']; ?>: <a href="#" onclick="rotateImage(-90)">&nbsp;L&nbsp;</a> &lt;&gt; <a href="#" onclick="rotateImage(90)">&nbsp;R&nbsp;</a></div>
		</div>
		<div id="labelprocessingdiv" style="clear:both;">
			<div id="labeldiv-<?php echo $currentImageId; ?>">
				<div>
					<img id="activeimg-<?php echo $currentImageId; ?>" src="<?php echo($imgUrlCollection[$currentImageId]) ?>" style="height:400px;" onload="initImageTool('activeimg-<?php echo $currentImageId; ?>')" />
				</div>
				<div style="width:100%; clear:both;">
					<div style="float:right; margin-right:20px; font-weight:bold;">
						<span id="current-image-index" style="display:none;"><?php echo $currentImageId; ?></span>
						<span id="image-count">Image <?php echo ($currentImageId + 1); ?> of <?php echo count($imgUrlCollection); ?></span>
						<?php if(count($imgUrlCollection) > 1): ?>
							<input type="hidden" id="image-collection-input" value='<?php echo json_encode($imgUrlCollection); ?>'>
							<a href="#" onclick="return nextProcessingImage();">>&gt;</a>
						<?php endif; ?>
					</div>
				</div>
				<div style="width:100%;clear:both;">
					<h4 style="text-align:left;">
						Choose your OCR model
					</h4>
					<fieldset class="" style="text-align:left; margin-bottom:15px">
						<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
							<div>
								<label for="ocr-method">Select OCR Method:</label>
								<select name="ocr-method" id="ocr-method">
									<?php
										$default = "tesseract";
										foreach ($availalbe_OCR as $key => $label) {
											$selected = ($key === $default) ? "selected" : "";
											echo "<option value=\"$key\" $selected>$label</option>\n";
										}
									?>
								</select>
							</div>
							<div>
								<label>
									<input type="checkbox" id="ocr-full" value="1" />
									<?php echo $LANG['OCR_WHOLE_IMG']; ?>
								</label><br />
								<label>
									<input type="checkbox" id="ocr-analysis" value="1" />
									<?php echo $LANG['OCR_ANALYSIS']; ?>
								</label>
							</div>
						</div>
						<div style="margin-top:15px">
						<button 
							value="OCR Image" 
							onclick="
								const imgElem = document.getElementById('activeimg-<?php echo $currentImageId; ?>');
								const imgUrl = imgElem ? imgElem.src : '';
								quickEntryOcrImage(this, <?php echo $imgId; ?>, <?php echo $currentImageId; ?>, imgUrl);
							">
							<?php echo $LANG['OCR_IMAGE']; ?>
						</button>
							<img id="workingcircle-tess-<?php echo $currentImageId; ?>" src="../../images/workingcircle.gif" style="display:none;" />
						</div>
					</fieldset>
				</div>
				<div style="width:100%;clear:both;">
					<div id="QEtfadddiv-<?php echo $currentImageId; ?>" style="">
						<!-- save TODO: need to update this correctly -->
						<form id="ocrform" name="ocrform" action="occurrencequickentry.php" method="post" onsubmit="return verifyFullForm(this);">
							<div>
								<textarea id="rawtext" name="rawtext" rows="12" cols="48" style="width:97%;background-color:#F8F8F8;"><?php echo $notesValue; ?></textarea>
							</div>
							<div title="OCR Notes" style="text-align:left; margin-top:10px">
								<b><?php echo $LANG['NOTES']; ?>:</b>
								<input name="rawnotes" type="text" value="" style="width:97%;" />
							</div>
							<div title="OCR Source" style="text-align:left;">
								<b><?php echo $LANG['SOURCE']; ?>:</b>
								<input name="rawsource" type="text" value="" style="width:97%;" />
							</div>
							<div style="float:left">
								<input type="hidden" name="imgid" value="<?php echo $imgId; ?>" />
								<input type="hidden" name="occid" value="<?php echo $occId; ?>" />
								<input type="hidden" name="collid" value="<?php echo $collId; ?>" />
								<input type="hidden" name="occindex" value="<?php echo $occIndex; ?>" />
								<input type="hidden" name="csmode" value="<?php echo $crowdSourceMode; ?>" />
								<input type="hidden" name="batchid" value="<?php echo $batchId; ?>" />
								<input type="hidden" name="imgindex" value="<?php echo $currentImgIndex; ?>" />
								<input type="hidden" name="barcode" value="<?php echo $barcode; ?>" />
								<button id="updateButton" name="updateForm" name="updateForm" value="Validate" onclick="return handleUpdateButtonClick()" style="margin-top:10px;"><?php echo ("Validate"); ?></button>
								<button id="SaveOCRButton" name="saveOCR" value="SaveOCR"  style="margin-top:10px;" onclick="return saveOCRResults()"><?php echo $LANG['SAVE_OCR']; ?></button>
							</div>
						</form>
					</div>
					<div id="QEtfeditdiv-<?php echo $currentImageId; ?>" style="clear:both;">
						<?php
						if(array_key_exists($imgId,$fragArr)){
							$fragCnt = 1;
							$targetPrlid = '';
							if(isset($newPrlid) && $newPrlid) $targetPrlid = $newPrlid;
							if(array_key_exists('editprlid',$_REQUEST)) $targetPrlid = $_REQUEST['editprlid'];
							foreach($fArr as $prlid => $rArr){
								$displayBlock = 'none';
								if($targetPrlid){
									if($prlid == $targetPrlid){
										$displayBlock = 'block';
									}
								}
								elseif($fragCnt==1){
									$displayBlock = 'block';
								}
								?>
								<div id="tfdiv-<?php echo $currentImageId.'-'.$fragCnt; ?>" style="display:<?php echo $displayBlock; ?>;">
									<form id="tfeditform-<?php echo $prlid; ?>" name="tfeditform-<?php echo $prlid; ?>" method="post" action="occurrenceeditor.php">
										<div>
											<textarea name="rawtext" rows="12" cols="48" style="width:97%"><?php echo $rArr['raw']; ?></textarea>
										</div>
										<div title="OCR Notes" style="text-align:left;">
											<b><?php echo $LANG['NOTES']; ?>:</b>
											<input name="rawnotes" type="text" value="<?php echo $rArr['notes']; ?>" style="width:97%;" />
										</div>
										<div title="OCR Source" style="text-align:left;">
											<b><?php echo $LANG['SOURCE']; ?>:</b>
											<input name="rawsource" type="text" value="<?php echo $rArr['source']; ?>" style="width:97%;" />
										</div>
										<div style="float:left;margin-left:10px;">
											<input type="hidden" name="editprlid" value="<?php echo $prlid; ?>" />
											<input type="hidden" name="collid" value="<?php echo $collId; ?>" />
											<input type="hidden" name="occid" value="<?php echo $occId; ?>" />
											<input type="hidden" name="occindex" value="<?php echo $occIndex; ?>" />
											<input type="hidden" name="csmode" value="<?php echo $crowdSourceMode; ?>" />
											<button name="submitaction" type="submit" value="Save OCR Edits" ><?php echo $LANG['SAVE_OCR_EDITS']; ?></button>
										</div>
										<div style="float:left;margin-left:20px;">
											<input type="hidden" name="iurl" value="<?php echo $iUrl; ?>" />
											<input type="hidden" id="cnumber" name="cnumber" value="<?php echo array_key_exists('catalognumber',$occArr)?$occArr['catalognumber']:''; ?>" />
											<?php
											if(isset($NLP_SALIX_ACTIVATED) && $NLP_SALIX_ACTIVATED){
												echo '<input name="salixocr" type="button" value="SALIX Parser" onclick="nlpSalix(this,'.$prlid.')" />';
												echo '<img id="workingcircle_salix-'.$prlid.'" src="../../images/workingcircle.gif" style="display:none;" />';
											}
											if(isset($NLP_LBCC_ACTIVATED) && $NLP_LBCC_ACTIVATED){
												echo '<input id="nlplbccbutton" name="nlplbccbutton" type="button" value="LBCC Parser" onclick="nlpLbcc(this,'.$prlid.')" />';
												echo '<img id="workingcircle_lbcc-'.$prlid.'" src="../../images/workingcircle.gif" style="display:none;" />';
											}
											?>
										</div>
									</form>
									<div style="float:right;font-weight:bold;margin-right:20px;">
										<?php
										echo $fragCnt.' of '.count($fArr);
										if(count($fArr) > 1){
											?>
											<a href="#" onclick="return nextRawText(<?php echo $currentImageId.','.($fragCnt+1); ?>)">=&gt;&gt;</a>
											<?php
										}
										?>
									</div>
									<div style="clear:both;">
										<form name="tfdelform-<?php echo $prlid; ?>" method="post" action="occurrenceeditor.php" style="margin-left:10px;width:100px;" >
											<input type="hidden" name="delprlid" value="<?php echo $prlid; ?>" />
											<input type="hidden" name="collid" value="<?php echo $collId; ?>" />
											<input type="hidden" name="occid" value="<?php echo $occId; ?>" /><br/>
											<input type="hidden" name="occindex" value="<?php echo $occIndex; ?>" />
											<input type="hidden" name="csmode" value="<?php echo $crowdSourceMode; ?>" />
											<button name="submitaction" type="submit" value="Delete OCR" ><?php echo $LANG['DELETE_OCR']; ?></button>
										</form>
									</div>
								</div>
								<?php
								$fragCnt++;
							}
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
</div>