<?php
include_once('../../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/OccurrenceEditorManager.php');
header("Content-Type: text/plain; charset=".$CHARSET);

$occManager = new OccurrenceEditorManager();

$imgId = isset($_POST['imgid']) ? filter_var($_POST['imgid'], FILTER_SANITIZE_NUMBER_INT) : null;
$notes = isset($_POST['rawtext']) ? trim($_POST['rawtext']) : null;
$rawNotes = isset($_POST['rawnotes']) ? filter_var($_POST['rawnotes'], FILTER_SANITIZE_STRING) : null;
$rawSource = isset($_POST['rawsource']) ? filter_var($_POST['rawsource'], FILTER_SANITIZE_STRING) : null;

$status = $occManager->saveOcrResult($imgId, $notes, $rawNotes, $rawSource);

echo $status;
?>