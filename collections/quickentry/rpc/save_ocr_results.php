<?php
include_once('../../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/OccurrenceEditorManager.php');
header("Content-Type: text/plain; charset=".$CHARSET);

$occManager = new OccurrenceEditorManager();

$imgId = isset($_POST['imgid']) ? filter_var($_POST['imgid'], FILTER_SANITIZE_NUMBER_INT) : null;
$notes = isset($_POST['rawtext']) ? trim($_POST['rawtext']) : null;
$rawNotes = isset($_POST['rawnotes']) ? trim(strip_tags($_POST['rawnotes'])) : null;
$rawSource = isset($_POST['rawsource']) ? trim(strip_tags($_POST['rawsource'])) : null;

$status = $occManager->saveOcrResult($imgId, $notes, $rawNotes, $rawSource);

echo $status;
?>