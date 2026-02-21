<?php
include_once('../../config/symbini.php');
// TODO: double check what is this file for
include_once($SERVER_ROOT.'/classes/OccurrenceEditorDeterminations.php');
if($LANG_TAG != 'en' && file_exists($SERVER_ROOT.'/content/lang/collections/editor/transcribe.'.$LANG_TAG.'.php')) include_once($SERVER_ROOT.'/content/lang/collections/editor/transcribe.'.$LANG_TAG.'.php');
else include_once($SERVER_ROOT.'/content/lang/collections/editor/transcribe.en.php');
header("Content-Type: text/html; charset=".$CHARSET);

if(!$SYMB_UID) header('Location: ../../profile/index.php?refurl=../collections/editor/transcribe.php?'.htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES));

$crowdSourceMode = array_key_exists('csmode', $_REQUEST) ? filter_var($_REQUEST['csmode'], FILTER_SANITIZE_NUMBER_INT) : 0;
$goToMode = array_key_exists('gotomode', $_REQUEST) ? filter_var($_REQUEST['gotomode'], FILTER_SANITIZE_NUMBER_INT) : 0;

$occManager = new OccurrenceEditorDeterminations();
$occManager->setCollId($collid);
$collMap = $occManager->getCollMap();
$collid = array_key_exists('collid', $_REQUEST) ? (int)$_REQUEST['collid'] : 0;
$qryCnt = $occManager->getQueryRecordCount();
$tabTarget = 0;

if($collMap){
	if($collMap['colltype']=='General Observations'){
		$isGenObs = 1;
		$collType = 'obs';
	}
	elseif($collMap['colltype']=='Observations'){
		$collType = 'obs';
	}
	$propArr = $occManager->getDynamicPropertiesArr();
	if(isset($propArr['modules-panel'])){
		foreach($propArr['modules-panel'] as $module){
			if(isset($module['paleo']['status']) && $module['paleo']['status']) $moduleActivation[] = 'paleo';
			elseif(isset($module['matSample']['status']) && $module['matSample']['status']){
				$moduleActivation[] = 'matSample';
				if($tabTarget > 3) $tabTarget++;
			}
		}
	}
}

$isEditor = 0;
$batchIds = $occManager->getBatch($collid);

if($IS_ADMIN || (array_key_exists("CollAdmin",$USER_RIGHTS) && in_array($collid,$USER_RIGHTS["CollAdmin"]))){
	$isEditor = 1;
}
elseif(array_key_exists("CollEditor",$USER_RIGHTS) && in_array($collid,$USER_RIGHTS["CollEditor"])){
	$isEditor = 1;
}
$statusStr = '';

// -----------------------------
// Batch selection + navigation defaults
// -----------------------------
$selectedBatchID = 0;
if(isset($_REQUEST['batchID']) && $_REQUEST['batchID'] !== ''){
	$selectedBatchID = (int)$_REQUEST['batchID'];
}
elseif(isset($_REQUEST['batchid']) && $_REQUEST['batchid'] !== ''){
	// occurrencequickentry.php uses batchid (lowercase d)
	$selectedBatchID = (int)$_REQUEST['batchid'];
}

$imgIDs = array();
if($selectedBatchID > 0){
	$tmp = $occManager->getImgIDs($selectedBatchID);
	if(is_array($tmp)) $imgIDs = $tmp;
}

echo "<pre>";
echo "collid = $collid\n";
echo "selectedBatchID = $selectedBatchID\n";
echo "getImgIDs returned:\n";
var_dump($tmp);
echo "</pre>";
// exit;

// Initialize all vars used by the UI so we never emit undefined-variable warnings
$firstImgId = 0;
$firstIndex = 0;
$firstBarcode = 0;
$firstOccId = 0;

$lastImgId = 0;
$lastIndex = 0;
$lastBarcode = 0;
$lastOccId = 0;

$lastEditIndex = 0;
$lastEditImgId = 0;
$lastEditBarcode = 0;
$lastEditOccId = 0;

$occData = array();
$hasBatch = false;

if(!empty($imgIDs)){
	$hasBatch = true;
	$firstIndex = 0;
	$firstImgId = (int)$imgIDs[0];
	$firstBarcode = (int)($occManager->getBarcode($firstImgId) ?: 0);

	$lastIndex = count($imgIDs) - 1;
	$lastImgId = (int)$imgIDs[$lastIndex];
	$lastBarcode = (int)($occManager->getBarcode($lastImgId) ?: 0);

	foreach ($imgIDs as $imgID) {
		$occData[$imgID] = $occManager->getOneOccID($imgID);
	}

	$firstOccId = (int)($occData[$firstImgId] ?? 0);
	$lastOccId = (int)($occData[$lastImgId] ?? 0);

	// Last edit is only meaningful within a batch context
	$lastEditIndex = (int)($occManager->getlastEdit($selectedBatchID) ?: 0);
	if($lastEditIndex > 0){
		$lastEditArr = $occManager->getBatchRec($selectedBatchID, $lastEditIndex);
		if($lastEditArr){
			$lastEditImgId = (int)($lastEditArr['imgid'] ?? 0);
			$lastEditBarcode = (int)($lastEditArr['catalogNumber'] ?? 0);
			$lastEditOccId = (int)($lastEditArr['occid'] ?? 0);
		}
	}
	if($lastEditImgId){
		$lastEditOccId = (int)($occData[$lastEditImgId] ?? $lastEditOccId);
	}
}

?>

<html>
	<head>
	    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $CHARSET;?>">
		<title><?php echo $DEFAULT_TITLE.$LANG['IMAGE_BATCH']; ?></title>
		<?php
		$activateJQuery = true;
		if(file_exists($SERVER_ROOT.'/includes/head.php')){
			include_once($SERVER_ROOT.'/includes/head.php');
		}
		else{
			echo '<link href="'.$CLIENT_ROOT.'/css/jquery-ui.css" type="text/css" rel="stylesheet" />';
			echo '<link href="'.$CLIENT_ROOT.'/css/basse.css?ver=1" type="text/css" rel="stylesheet" />';
			echo '<link href="'.$CLIENT_ROOT.'/css/symbiota/quickentry.css" type="text/css" rel="stylesheet" />';
		}
		?>
		<script src="<?php echo $CLIENT_ROOT; ?>/js/jquery-3.7.1.min.js" type="text/javascript"></script>
		<script src="<?php echo $CLIENT_ROOT; ?>/js/jquery-ui.min.js" type="text/javascript"></script>
		<script src="<?php echo $CLIENT_ROOT; ?>/js/symb/collections.editor.query.js" type="text/javascript"></script>
		<script src="<?php echo $CLIENT_ROOT; ?>/js/symb/collections.editor.main.js" type="text/javascript"></script>
		<script type="text/javascript">
			function navigateToRecordNew(crowdSourceMode, gotomode, collId, batchId, imgId, imgIndex, barcode, occId, occIndex) {
				if(barcode == null && occId == null) {
					var url = 'occurrencequickentry.php?gotomode=' + gotomode + '&collid=' + collId + '&imgid=' + imgId + '&imgindex=' + imgIndex;
				} else {
					var url = 'occurrencequickentry.php?csmode=' + crowdSourceMode + '&collid=' + collId +'&batchid=' + batchId + '&imgid=' + imgId + '&imgindex=' + imgIndex + '&barcode=' + barcode + '&occid=' + occId + '&occindex=' + occIndex;
				}
				window.location.href = url;
				event.preventDefault();
			}
		</script>
	</head>
	<body>
	<?php
	include($SERVER_ROOT.'/includes/header.php');
	?>
	<div class='navpath'>
		<a href='../../index.php'><?php echo $LANG['HOME']; ?></a> &gt;&gt;
		<a href="../misc/collprofiles.php?collid=<?php echo $collid; ?>&emode=1"><?php echo $LANG['COLL_MANAGE']; ?></a> &gt;&gt;
		<b><?php echo $LANG['BATCH_DETERS']; ?></b>
	</div>
	<!-- This is inner text! -->
	<div id="innertext">
		<?php
		if($isEditor){
			?>
			<div style="margin:0px;">
				<fieldset style="padding:10px;">
					<legend><b><?php echo $LANG['TRANSCRIBE_INTO_SPECIFY']; ?></b></legend>
					<div style="margin:15px;width:700px;">
                        <!-- TODO: update the submit function of the form -->
						<form name="batchform" method="post">
							<div style="margin-bottom:15px; align-items: center;">
								<h4 style="margin-right: 15px;">Work On batch: <?php echo ($selectedBatchID > 0 ? htmlspecialchars((string)$selectedBatchID, ENT_QUOTES) : 'None Selected'); ?></h4>
								<div style="display: flex; flex-grow: 1;">
									<button type="button" <?php echo (!$hasBatch ? "disabled" : ""); ?> name="first" style="flex-grow: 0.5; margin-right: 5px;" onclick="return navigateToRecordNew(<?php echo ($crowdSourceMode).', '.($goToMode).', '.($collid).', '.($selectedBatchID).', '.($firstImgId).', '.($firstIndex).', '.($firstBarcode).', '.($firstOccId).', '.($firstIndex) ; ?>)"><?php echo $LANG['START_FROM']; ?> first.</button>
									<button type="button" <?php echo (!$hasBatch ? "disabled" : ""); ?> name="last" style="flex-grow: 0.5; margin-right: 5px;" onclick="return navigateToRecordNew(<?php echo ($crowdSourceMode).', '.($goToMode).', '.($collid).', '.($selectedBatchID).', '.($lastImgId).', '.($lastIndex).', '.($lastBarcode).', '.($lastOccId).', '.($lastIndex); ?>)"><?php echo $LANG['START_FROM']; ?> last.</button>
									<button type="button" <?php echo (!$hasBatch ? "disabled" : ""); ?> name="lastView" style="flex-grow: 0.5;" onclick="return navigateToRecordNew(<?php echo ($crowdSourceMode).', '.($goToMode).', '.($collid).', '.($selectedBatchID).', '.($lastEditImgId).', '.($lastEditIndex).', '.($lastEditBarcode).', '.($lastEditOccId).', '.($lastEditIndex); ?>)"><?php echo $LANG['START_FROM']; ?> last edit.</button>
								</div>
							</div>
							<div>
								<b><?php echo $LANG['WORK_ON_BATCH']; ?></b>
								<select id="batchID" name="batchID" style="width:400px;" onchange="this.form.submit()">
									<option value="">-- Select Batch --</option>
									<?php
									foreach ($batchIds as $batchID) {
										$batch_name = current($occManager->getbatchName($batchID));
										$selected = ($batchID == $selectedBatchID) ? " selected" : "";
										echo "<option value=\"$batchID\"$selected>$batch_name</option>";
									}
									?>
								</select>
							</div>
						</form>
					</div>
				</fieldset>
				<!-- TODO: need to figure out what this status is -->
				<!-- <fieldset>
					<div>
						<p style="margin:0px;"><?php // echo $LANG['STATUS']; ?></p>
					</div>
				</fieldset> -->
			</div>
			<?php
		}
		else{
			?>
			<div style="font-weight:bold;margin:20px;font-weight:150%;">
				<?php echo $LANG['NO_PERMISSIONS']; ?>
			</div>
			<?php
		}
		?>
	</div>
	<?php
	include($SERVER_ROOT.'/includes/footer.php');
	?>
	</body>
</html>