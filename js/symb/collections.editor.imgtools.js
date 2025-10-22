var activeImgIndex = 1;
var ocrFragIndex = 1;

$(document).ready(function() {
	//Remember image popout status 
	var imgTd = getCookie("symbimgtd");
	if(imgTd != "close") toggleImageTdOn();
	//if(imgTd == "open" || csMode == 1) toggleImageTdOn();
	initImgRes();
});

function toggleImageTdOn(){
	var imgSpan = document.getElementById("imgProcOnSpan");
	if(imgSpan){
		imgSpan.style.display = "none";
		document.getElementById("imgProcOffSpan").style.display = "block";
		var imgTdObj = document.getElementById("imgtd");
		if(imgTdObj){
			document.getElementById("imgtd").style.display = "block";
			initImageTool("activeimg-1");
			//Set cookie to tag td as open
	        document.cookie = "symbimgtd=open";
		}
	}
}

function toggleImageTdOff(){
	var imgSpan = document.getElementById("imgProcOnSpan");
	if(imgSpan){
		imgSpan.style.display = "block";
		document.getElementById("imgProcOffSpan").style.display = "none";
		var imgTdObj = document.getElementById("imgtd");
		if(imgTdObj){
			document.getElementById("imgtd").style.display = "none";
			//Set cookie to tag td closed
	        document.cookie = "symbimgtd=close";
		}
	}
}

function initImageTool(imgId){
	var img = document.getElementById(imgId);
	if(!img.complete){
		imgWait=setTimeout(function(){initImageTool(imgId)}, 500);
	}
	else{
		var portWidth = 400;
		var portHeight = 400;
		var portXyCookie = getCookie("symbimgport");
		if(portXyCookie){
			portWidth = parseInt(portXyCookie.substr(0,portXyCookie.indexOf(":")));
			portHeight = parseInt(portXyCookie.substr(portXyCookie.indexOf(":")+1));
		}
		$(function() {
			$(img).imagetool({
				maxWidth: 6000
				,viewportWidth: portWidth
		        ,viewportHeight: portHeight
			});
		});
	}
}

function setPortXY(portWidth,portHeight){
	document.cookie = "symbimgport=" + portWidth + ":" + portHeight;
}

function initImgRes(){
	var imgObj = document.getElementById("activeimg-"+activeImgIndex);
	if(imgObj){
		if(imgLgArr[activeImgIndex]){
			var imgRes = getCookie("symbimgres");
			if(imgRes == 'lg') changeImgRes('lg');
		}
		else{
			imgObj.src = imgArr[activeImgIndex];
			document.getElementById("imgresmed").checked = true;
			var imgResLgRadio = document.getElementById("imgreslg");
			imgResLgRadio.disabled = true;
			imgResLgRadio.title = "Large resolution image not available";
		}
		if(imgArr[activeImgIndex]){
			//Do nothing
		}
		else{
			if(imgLgArr[activeImgIndex]){
				imgObj.src = imgLgArr[activeImgIndex];
				document.getElementById("imgreslg").checked = true;
				var imgResMedRadio = document.getElementById("imgresmed");
				imgResMedRadio.disabled = true;
				imgResMedRadio.title = "Medium resolution image not available";
			}
		}
	}
}

function changeImgRes(resType){
	var imgObj = document.getElementById("activeimg-"+activeImgIndex);
	var oldSrc = imgObj.src;
	if(resType == 'lg'){
        document.cookie = "symbimgres=lg";
    	if(imgLgArr[activeImgIndex]){
    		imgObj.src = imgLgArr[activeImgIndex];
    		document.getElementById("imgreslg").checked = true;
    	}
	}
	else{
        document.cookie = "symbimgres=med";
    	if(imgArr[activeImgIndex]){
    		imgObj.src = imgArr[activeImgIndex];
    		document.getElementById("imgresmed").checked = true;
    	}
	}
}

function rotateImage(rotationAngle, imgIndex){
	if(typeof imgIndex === 'undefined') {
		imgIndex = activeImgIndex;
	}
	
	var imgObj = document.getElementById("activeimg-"+imgIndex);
	if(!imgObj){
		console.error("Image element not found: activeimg-" + imgIndex);
		return;
	}
	
	var imgAngle = 0;
	if(imgObj.style.transform){
		var transformValue = imgObj.style.transform;
		var match = transformValue.match(/rotate\((-?\d+)deg\)/);
		if(match){
			imgAngle = parseInt(match[1]);
		}
	}
	
	imgAngle = imgAngle + rotationAngle;
	if(imgAngle < 0) imgAngle = 360 + imgAngle;
	else if(imgAngle >= 360) imgAngle = imgAngle % 360;
	
	imgObj.style.transform = "rotate("+imgAngle+"deg)";
	
	if(typeof $ !== 'undefined' && $(imgObj).data('imagetool')){
		$(imgObj).imagetool("option","rotationAngle",imgAngle);
		$(imgObj).imagetool("reset");
	}
}

function ocrImage(ocrButton, target, imgidVar, imgCnt){
	ocrButton.disabled = true;
	let wcElem = document.getElementById("workingcircle-"+target+"-"+imgCnt);
	wcElem.style.display = "inline";
	
	let imgObj = document.getElementById("activeimg-"+imgCnt);
	let xVar = 0;
	let yVar = 0;
	let wVar = 1;
	let hVar = 1;
	let ocrBestVar = 0;

	if(document.getElementById("ocrfull-"+target).checked == false){
		xVar = $(imgObj).imagetool("properties").x;
		yVar = $(imgObj).imagetool("properties").y;
		wVar = $(imgObj).imagetool("properties").w;
		hVar = $(imgObj).imagetool("properties").h;
	}
	if(document.getElementById("ocrbest").checked == true){
		ocrBestVar = 1;
	}

	$.ajax({
		type: "POST",
		url: "rpc/ocrimage.php",
		data: { imgid: imgidVar, target: target, ocrbest: ocrBestVar, x: xVar, y: yVar, w: wVar, h: hVar }
	}).done(function( msg ) {
		let rawStr = msg;
		document.getElementById("tfeditdiv-"+imgCnt).style.display = "none";
		document.getElementById("tfadddiv-"+imgCnt).style.display = "block";
		let addform = document.getElementById("ocraddform-"+imgCnt);
		addform.rawtext.innerText = rawStr;
		addform.rawtext.textContent = rawStr;
		//Add OCR source with date
		let today = new Date();
		let dd = today.getDate();
		let mm = today.getMonth()+1; //January is 0!
		let yyyy = today.getFullYear();
		if(dd<10) dd='0'+dd;
		if(mm<10) mm='0'+mm;
		if(target == "tess") target = "Tesseract";
		else target = "Digi-Leap";
		addform.rawsource.value = target+": "+yyyy+"-"+mm+"-"+dd;
		
		wcElem.style.display = "none";
		ocrButton.disabled = false;
	});
}

function nlpLbcc(nlpButton,prlid){
	document.getElementById("workingcircle_lbcc-"+prlid).style.display = "inline";
	nlpButton.disabled = true;
	var f = nlpButton.form;
	var rawOcr = f.rawtext.innerText;
	if(!rawOcr) rawOcr = f.rawtext.textContent;
	var cnumber = f.cnumber.value;
	var collid = f.collid.value;
	//alert("rpc/nlplbcc.php?collid="+collid+"&catnum="+cnumber+"&rawocr="+rawOcr);
	$.ajax({
		type: "POST",
		url: "rpc/nlplbcc.php",
		data: { rawocr: rawOcr, collid: collid, catnum: cnumber }
	}).done(function( msg ) {
		pushDwcArrToForm(msg, "#ebbb7f");
		nlpButton.disabled = false;
		document.getElementById("workingcircle_lbcc-"+prlid).style.display = "none";
	});
}

function nlpSalix(nlpButton,prlid){
	document.getElementById("workingcircle_salix-"+prlid).style.display = "inline";
	nlpButton.disabled = true;
	var f = nlpButton.form;
	var rawOcr = f.rawtext.innerText;
	if(!rawOcr) rawOcr = f.rawtext.textContent;
	//alert("rpc/nlpsalix.php?rawocr="+rawOcr);
	$.ajax({
		type: "POST",
		url: "rpc/nlpsalix.php",
		data: { rawocr: rawOcr }
	}).done(function( msg ) {
		pushDwcArrToForm(msg,"#77dd77");
		nlpButton.disabled = false;
		document.getElementById("workingcircle_salix-"+prlid).style.display = "none";
	});
}

function pushDwcArrToForm(msg,bgColor){
	try{
		var dwcArr = $.parseJSON(msg);
		var f = document.fullform;
		//var fieldsTransfer = "";
		//var fieldsSkip = "";
		var scinameTransferred = false;
		var verbatimElevTransferred = false;
		for(var k in dwcArr){
			try{
				if(k != 'family' && k != 'scientificnameauthorship'){
					var elem = f.elements[k];
					var inVal = dwcArr[k];
					if(inVal && elem && elem.value == "" && elem.disabled == false && elem.type != "hidden"){
						if(k == "sciname") scinameTransferred = true;
						if(k == "verbatimelevation") verbatimElevTransferred = true;
						elem.value = inVal;
						elem.style.backgroundColor = bgColor;
						//fieldsTransfer = fieldsTransfer + ", " + k;
						fieldChanged(k);
					}
					else{
						//fieldsSkip = fieldsSkip + ", " + k;
					}
				}
			}
			catch(err){
				//alert(err);
			}
		}
		if(scinameTransferred) verifyFullFormSciName();
		if(verbatimElevTransferred) parseVerbatimElevation(f);
		//if(fieldsTransfer == "") fieldsTransfer = "none";
		//if(fieldsSkip == "") fieldsSkip = "none";
		//alert("Field parsed: " + fieldsTransfer + "\nFields skipped: " + fieldsSkip);
	}
	catch(err){
		//JSON parsing error
		//alert(msg);
		alert(err);
	}
	
}

function nextLabelProcessingImage(imgCnt){
	document.getElementById("labeldiv-"+(imgCnt-1)).style.display = "none";
	var imgObj = document.getElementById("labeldiv-"+imgCnt);
	if(!imgObj){
		imgObj = document.getElementById("labeldiv-1");
		imgCnt = "1";
	}
	imgObj.style.display = "block";
	
	initImageTool("activeimg-"+imgCnt);
	activeImgIndex = imgCnt;
	
	return false;
}

function nextRawText(imgCnt,fragCnt){
	document.getElementById("tfdiv-"+imgCnt+"-"+(fragCnt-1)).style.display = "none";
	var fragObj = document.getElementById("tfdiv-"+imgCnt+"-"+fragCnt);
	if(!fragObj) fragObj = document.getElementById("tfdiv-"+imgCnt+"-1");
	fragObj.style.display = "block";
	ocrFragIndex = fragCnt;
	return false;
}

function quickEntryOcrImage(ocrButton, imgidVar, imgCnt, imgURl) {
	console.log("Function quickEntryOcrImage called");
	console.log(imgURl);
	imgCnt = 0; // Reset image counter
	ocrButton.disabled = true; // Disable button to prevent multiple clicks

	// Show loading spinner
	let wcElem = document.getElementById("workingcircle-tess-" + imgCnt);
	wcElem.style.display = "inline";

	// Get selected OCR 
	let target = document.getElementById("ocr-method").value;

	let ocrUrl = "";
	if (target === "external") {
		ocrUrl = "../quickentry/rpc/externalocr.php";
	} else if (target === "others") {
		ocrUrl = "../quickentry/rpc/otherocr.php";
	} else {
		ocrUrl = "../quickentry/rpc/ocrimage.php";
	}

	$.ajax({
		type: "POST",
		url: ocrUrl,
		data: { imgid: imgidVar, target: target, imgurl: imgURl },
		success: function(response) {
			let decodedResponse;
			if (typeof response === "string") {
				try {
					decodedResponse = JSON.parse(response);
				} catch (e) {
					console.error("Error parsing JSON response:", e);
					decodedResponse = response;
				}
			} else {
				decodedResponse = response;
			}

			let plainTextResponse = "";
			if (typeof decodedResponse === "object") {
				for (const [key, value] of Object.entries(decodedResponse)) {
					plainTextResponse += `${key}: ${value}\n`;
				}
			} else {
				plainTextResponse = decodedResponse;
			}

			// Store in global variable for later use
			storedOcrResponse = plainTextResponse;

			// Update the textarea
			let rawtextBox = document.getElementById("rawtext");
			rawtextBox.value = plainTextResponse;

			wcElem.style.display = "none";
			ocrButton.disabled = false;
		},
		error: function(xhr, status, error) {
			storedOcrResponse = "OCR Failed";
			console.error("External OCR Error: ", error);
			wcElem.style.display = "none";
			ocrButton.disabled = false;
		}
	});
}

let storedOcrResponse = "";
// this state variable used to check the state of the textBox, either "needsValidation" or "ready" to populate the fieldss
let updateState = "needsValidation";  

function handleUpdateButtonClick() {
	if (updateState === "needsValidation") {
		confirmOCRresult();
		updateState = "ready";
		const btn = document.getElementById("updateButton");
		btn.innerText = "Update Form";
		btn.value = "Update Form";
		return false;
	} else {
		return UpdateFromWithOCR();
	}
}

// detect changes in the rawtext textarea
window.addEventListener('DOMContentLoaded', function () {
	const rawtextBox = document.getElementById("rawtext");
	const updateButton = document.getElementById("updateButton");

	if (rawtextBox) {
		console.log("textBox udpated");
		rawtextBox.addEventListener("input", function () {
			updateState = "needsValidation";
			updateButton.innerText = "Validate";
			updateButton.value = "Validate";
		});
	}
});

function confirmOCRresult() {
	if (event) event.preventDefault();

	let rawtextBox = document.getElementById("rawtext");

	if (!rawtextBox) {
		console.error("No OCR result box found");
		return false;
	}

	let rawText = rawtextBox.value.trim();

	if (rawText === "") {
		console.error("OCR result is empty");
		alert("OCR result is empty!");
		return false;
	}

	let lines = rawText.split("\n");
	let allValid = true;

	lines.forEach((line, index) => {
		line = line.trim();
		if (line === "") return; // skip empty lines

		// Check for colon separator
		if (!line.includes(": ")) {
			console.error(`Line ${index + 1} is invalid: missing ': ' separator.`);
			alert(`Error: Line ${index + 1} is invalid. Missing ': ' separator.`);
			allValid = false;
			return;
		}

		// Split key and value
		let [key, ...valueParts] = line.split(": ");
		let value = valueParts.join(": ").trim();

		// Check key and value are not empty
		if (!key.trim() || !value) {
			console.error(`Line ${index + 1} is invalid: key or value is empty.`);
			alert(`Error: Line ${index + 1} has an empty key or value.`);
			allValid = false;
			return;
		}

		// Check for unwanted quotes in key or value
		if (key.includes('"') || value.includes('"')) {
			console.error(`Line ${index + 1} is invalid: contains quotation marks.`);
			alert(`Error: Line ${index + 1} should not contain quotation marks.`);
			allValid = false;
			return;
		}
	});

	if (!allValid) {
		console.warn("OCR confirmation aborted due to invalid format.");
		return false;
	}

	storedOcrResponse = rawText;
	console.log("OCR response confirmed");
	return false;
}

function UpdateFromWithOCR() {
	if (!storedOcrResponse || storedOcrResponse.trim() === "") {
		console.error("No OCR response available");
		return false;
	}

	let lines = storedOcrResponse.split("\n");
	
	lines.forEach(line => {
		if (line.trim() === "") return;

		// Split key and value by ": "
		let [key, ...valueParts] = line.split(": ");
		let value = valueParts.join(": ").trim();
		let field = null;

		if (key === 'recordedBy') {
			field = document.getElementById("ffrecordedby");
		} else if (key === 'location') {
			field = document.getElementById("ffgeowithin");
		} else if  (key === 'scientificName') {
			field = document.getElementById("ffcurrname");
		} else if  (key === 'eventDate') {
			field = document.getElementById("ffeventdate");
		} else if  (key === 'barcode') {
			field = document.getElementById("barcode");
		} else if  (key === 'institutionCode') {
			field = document.getElementById("");
		} else if  (key === 'image_path') {
			field = document.getElementById("");
		} else {
			console.warn(`Unrecognized key '${key}' in OCR response.`);
			return;
		}

		// Find the input field by key
		if (field) {
			field.value = value;
			field.dispatchEvent(new Event("change"));

			// Find the corresponding label and bold it
			let fieldBlock = field.closest(".field-block");
			if (fieldBlock) {
				let label = fieldBlock.querySelector(".field-label");
				if (label) {
					label.classList.add("highlight-label");
				}
			}
		}
	});
	return false;
}

function saveOCRResults() {
    const rawTextElement = document.getElementById('rawtext');
    const rawNotesElement = document.querySelector('input[name="rawnotes"]');
    const rawSourceElement = document.querySelector('input[name="rawsource"]');
    const imgId = document.querySelector('input[name="imgid"]').value;

    if (!rawTextElement) {
        alert('Error: Could not find the rawtext textarea.');
        return false;
    }

    const rawTextContent = rawTextElement.value;
    const rawNotesContent = rawNotesElement ? rawNotesElement.value : '';
    const rawSourceContent = rawSourceElement ? rawSourceElement.value : '';

    if (!imgId) {
        alert('Error: Could not find the image ID.');
        return false;
    }

    // Create an AJAX request to send the data to the server
    const xhr = new XMLHttpRequest();
    const url = '../../collections/quickentry/rpc/save_ocr_results.php';

    const params = `imgid=${encodeURIComponent(imgId)}&rawtext=${encodeURIComponent(rawTextContent)}&rawnotes=${encodeURIComponent(rawNotesContent)}&rawsource=${encodeURIComponent(rawSourceContent)}`;

    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            alert(xhr.responseText || 'OCR results saved successfully.');
        } else {
            alert('Error saving OCR results. Please try again.');
            console.error('Request failed. Returned status of ' + xhr.status);
        }
    };

    xhr.onerror = function() {
        alert('There was a network error while trying to save.');
        console.error('There was a network error.');
    };

    xhr.send(params);

    return false;
}

$(function() {
	$( "#zoomInfoDialog" ).dialog({
		autoOpen: false,
		position: { my: "left top", at: "right bottom", of: "#zoomInfoDiv" }
	});

	$( "#zoomInfoDiv" ).click(function() {
		$( "#zoomInfoDialog" ).dialog( "open" );
	});
});

function floatImgPanel(){
	$( "#labelProcFieldset" ).css('position', 'fixed');
	$( "#labelProcFieldset" ).css('top', '20px');
	var pos = $( "#labelProcDiv" ).position();
	var posLeft = pos.left - $(window).scrollLeft();
	$( "#labelProcFieldset" ).css('left', posLeft);
	$( "#floatImgDiv" ).hide();
	$( "#draggableImgDiv" ).hide();
	$( "#anchorImgDiv" ).show();
}

function draggableImgPanel(){
	$( "#labelProcFieldset" ).draggable();
	$( "#labelProcFieldset" ).draggable({ cancel: "#labelprocessingdiv" });
	$( "#labelHeaderDiv" ).css('cursor', 'move');
	$( "#labelProcFieldset" ).css('top', '10px');
	$( "#labelProcFieldset" ).css('left', '5px');
	$( "#floatImgDiv" ).hide();
	$( "#draggableImgDiv" ).hide();
	$( "#anchorImgDiv" ).show();
}

function anchorImgPanel(){
	$( "#draggableImgDiv" ).show();
	$( "#floatImgDiv" ).show();
	$( "#anchorImgDiv" ).hide();
	$( "#labelProcFieldset" ).css('position', 'static');
	$( "#labelProcFieldset" ).css('top', '');
	$( "#labelProcFieldset" ).css('left', '');
	try {
		$( "#labelProcFieldset" ).draggable( "destroy" );
		$( "#labelHeaderDiv" ).css('cursor', 'default');
	}
	catch(err) {
	}
}

function nextProcessingImage() {
	var imgCollectionInput = document.getElementById('image-collection-input');
	var imgArr = JSON.parse(imgCollectionInput.value);
	var currentImageIndex = parseInt(document.getElementById('current-image-index').textContent);
	var totalImages = imgArr.length;
	var nextImageIndex = (currentImageIndex + 1) % totalImages; // This ensures the index loops back to 0

	// reference the new image URL from the JS array
	var newImgSrc = imgArr[nextImageIndex]; // This should be the URL of the next image

	// Update the display of the current image index and count
	document.getElementById('current-image-index').textContent = nextImageIndex;
	document.getElementById('image-count').textContent = 'Image ' + (nextImageIndex + 1) + ' of ' + totalImages;
	document.getElementById('activeimg').src = newImgSrc;

	// Optionally update the onload event for the new image
	document.getElementById('activeimg').onload = function() {
		initImageTool('activeimg-' + nextImageIndex);
	};

	return false;
}