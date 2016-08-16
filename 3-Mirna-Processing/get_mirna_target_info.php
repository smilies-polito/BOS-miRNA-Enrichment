<?php

/*
########S#Y#S#B#I#O###G#R#O#U#P#######L#I#C#E#N#S#E#####!!!###
### SySBio Group License
### License grant by
### SysBio Group
### Department of Control and Computer Engineering
### Politecnico di Torino
### Corso Duca degli Abruzzi 24
### 10129 - Torino, Italy.
###
### This is a worldwide license.
###
### This is a NO-commercial license, the source code under this license can be used only for research purpose.
###
### Use of this code for commercial applications must be granted by the SysBio group under request.
###
### In any case, you have to maintain the paternity of the opera under this license.
###
### Patents derived from usage of the opera under this license must be agreed by authors.
###
### ---------
### SySBio research group - Politecnico di Torino
### Contacts:
### Gianfranco POLITANO - gianfranco.politano@polito.it
### Stefano DI CARLO - stefano.dicarlo@polito.it
###
### People worked on this project:
### Gianfranco POLITANO - gianfranco.politano@polito.it
### Stefano DI CARLO - stefano.dicarlo@polito.it
### Alessandro SAVINO - alessandro.savino@polito.it
### Alfredo BENSO - alfredo.benso@polito.it
### ---------
#####################################################
*/


	function miriadParser (){
		$miriadList=array();
		$handle = fopen(dirname ( __FILE__ )."/../0-Databases/miriad_human_intragenic_v.2014.txt","r");
		while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
			$miriadList[strtolower($data[0])]["host"]= $data[11];
			$miriadList[strtolower($data[0])]["type"]= "intra";
			
		}
		fclose($handle);
		$handle = fopen(dirname ( __FILE__ )."/../0-Databases/miriad_human_intergenic_v.2014.txt","r");
		while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
			$miriadList[strtolower($data[0])]["host"]= $data[12];
			$miriadList[strtolower($data[0])]["type"]= "inter";
		}
		fclose($handle);
		return ($miriadList);

	}

	function hostgene ($mirna, $miriadList) {
		if (array_key_exists ($mirna , $miriadList )) {
			$result["hostgenesymbol"]=$miriadList[$mirna]["host"];
			$result["hostgeneurl"]="http://www.bioinfo.mochsl.org.br/miriad/miRNA/".$mirna."/";
			$result["type"]=$miriadList[$mirna]["type"];
		} else {
			$result["hostgenesymbol"]="";
			$result["hostgeneurl"]="";
		}
		return ($result);
	}


	function gettargets($mirna) {

		$targetTotaliArray = array();
		$targetMirTarbaseArray = array();

		if (file_exists(dirname ( __FILE__ )."/../0-Databases/targets-all/".$mirna.".json")) {
			$listOftotalTargets = file_get_contents(dirname ( __FILE__ )."/../0-Databases/targets-all/".$mirna.".json");
			$listOftotalTargets = json_decode($listOftotalTargets, true);
		} else {	
			$listOftotalTargets["rows"]=array();
		}

		
		if (file_exists(dirname ( __FILE__ )."/../0-Databases/targets-mirtarbase/".$mirna.".json")) {
			$listOfMirTarbaseTargets = file_get_contents(dirname ( __FILE__ )."/../0-Databases/targets-mirtarbase/".$mirna.".json");
			$listOfMirTarbaseTargets = json_decode($listOfMirTarbaseTargets, true);
		} else {	
			$listOfMirTarbaseTargets["rows"]=array();
		}
		
		// Save total targets 
		foreach ($listOftotalTargets["rows"] as $target){
			$part = explode (":",$target["id"]);
			$targetTotaliArray[] = $part[1];
		}

		// Save mirTarBase targets 
		foreach ($listOfMirTarbaseTargets["rows"] as $target){
			$part = explode (":",$target["id"]);
			$targetMirTarbaseArray[] = $part[1];
		}
		$result["total"]=array_unique($targetTotaliArray);
		$result["mirtarbase"]=array_unique($targetMirTarbaseArray);
		return $result;

	}

	function counttargets($targetTotaliArray, $targetMirTarbaseArray, $geneIDs) {

		// Array Intersect to find targets available in the pathway 
		$intersectTotal = array_intersect($geneIDs,$targetTotaliArray);
		$intersectMirTarbase = array_intersect($geneIDs,$targetMirTarbaseArray);

		$result["totaltarget"]=count($intersectTotal);
		$result["mirtarbasetargets"]=count($intersectMirTarbase);
		return($result);
	}

// ******************************************************************************************************************************
// START MAIN
// ******************************************************************************************************************************

	/* Includes the file containing the search to perform */
	include_once (dirname ( __FILE__ )."/../config.php");

	$miriadList = miriadParser();

	$mirnas= array();
	
	$handle1 = fopen($getmiRNAtargetscfg["mirnalistfile"],"r");
	/* skip the first line containing the header */
	fgetcsv($handle1, 1000, ";");


	/*process each mirna */
	while (($m = fgetcsv($handle1, 1000, ";")) !== FALSE) {
		$mirnas[]= $m[0];
	}
	fclose ($handle1);

	$pathwaydir = $getmiRNAtargetscfg["pathwaysdir"];
	$pathways=array();

	$handle1 = fopen($getmiRNAtargetscfg["pathwaylistfile"],"r");
	/* skip the first line containing the header */
	fgetcsv($handle1, 1000, ";");
	while (($p = fgetcsv($handle1, 1000, ";")) !== FALSE) {
		$pathwayid = $p[0];
		$filename = $pathwaydir.$pathwayid.".csv";
		$handle = fopen($filename,"r");
		$pathways[$pathwayid]= array ("genes" => array(), "geneIDs" => array(), "mirnas" => array());

		while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
		    $symbol = $data[0];
		    $id = $data[1];
		    $type = $data[2];
		    if ($type=="gene" || $type=="tf") {
			if ($id != ""){
				$pathways[$pathwayid]["genes"][]= $symbol;
				$pathways[$pathwayid]["geneIDs"][]= $id;
			}
		    }
		    if ($type=="intragenicMirna") {
				$pathways[$pathwayid]["mirnas"][]= $symbol;
			}
			
		}
		fclose ($handle);
	}
	fclose ($handle1);
	

	print ";;;";
	foreach ($pathways as $pathwayname => $pathway) {
		print ";$pathwayname; ; ";
	}
	print "\n";
	print "mirnaname;type;hostgene;Link to target";
	foreach ($pathways as $pathwayname => $pathway) {
		print ";hosted;target;valtarget";
	}
	print "\n";


	foreach ($mirnas as $mirna) {
		$hg =hostgene($mirna, $miriadList); 
		print "\"".$mirna."\";";
		print "\"".$hg["type"]."\";";
		print "\"".$hg["hostgenesymbol"]."\";";
		print "\"".$hg["hostgeneurl"]."\"";

		$targets =gettargets($mirna); 

		foreach ($pathways as $pathway) {
			//echo "Check: ".$mirna." ".$pathway;
			if (in_array($mirna,$pathway["mirnas"])) {
				print ";\"1\"";
			} else {
				print ";\"0\"";
			}
			
			$targetcount = counttargets($targets["total"],$targets["mirtarbase"],$pathway["geneIDs"]);


			print ";\"".$targetcount["totaltarget"]."\"";
			print ";\"".$targetcount["mirtarbasetargets"]."\"";
		}

		print "\n";


	}



?>
