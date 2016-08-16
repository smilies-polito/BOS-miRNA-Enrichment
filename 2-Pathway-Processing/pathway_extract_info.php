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


	/* Includes the file containing the search to perform */
	include_once (dirname ( __FILE__ )."/../config.php");

	// FILES & FOLDERS I/O
	$pathwaylist = $pathwaydwonloadercfg["pathwaylistfile"];
	$pathwaydir = $pathwaydwonloadercfg["outputdir"];
	$genesetstring = file_get_contents($argv[1]);

	$pathways=array();

	$geneset = json_decode($genesetstring, true);

	/* Open th file containing the list of pathways */
	$handle1 = fopen($pathwaylist,"r");
	/* skip the first line containing the header */
	fgetcsv($handle1, 1000, ";");

	/*process each pathway */
	while (($p = fgetcsv($handle1, 1000, ";")) !== FALSE) {
		$pathwayid = $p[0];
		$filename = $pathwaydir."/".$pathwayid.".csv";
		$handle = fopen($filename,"r");
		$pathways[$pathwayid]= array ("name" => $p[1], "genes" => array(), "genesIDs" => array(), "tfs" => array(), "tfsIDs" => array(),  "mirnas" => array());

		/* Count the number of genes and TF */
		while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
		    $symbol = $data[0];
		    $id = $data[1];
		    $type = $data[2];
	 	    if (!in_array($id,$geneset)) continue;
		    if ($type=="tf") {
			if ($id != ""){
				$pathways[$pathwayid]["tfs"][]= $symbol;
				$pathways[$pathwayid]["tfsIDs"][]= $id;
			}
		    }
		    if ($type=="gene") {
			if ($id != ""){
				$pathways[$pathwayid]["genes"][]= $symbol;
				$pathways[$pathwayid]["genesIDs"][]= $id;
			}
		    }
		}
		fclose ($handle);
	}
	fclose ($handle1);



	print "Id;Name;TFs;Genes;Total\n";
	foreach ($pathways as $id => $pathway) {

		/* When computing the totals I have to consider that a TF could be already
		part of a pathway. In thi scase it must be considered only once */

		$totalnodes = array_unique (array_merge($pathway["tfs"],$pathway["genes"]));

		print $id.";".$pathway["name"].";".count(array_unique($pathway["tfs"])).";".count(array_unique($pathway["genes"])).";".count($totalnodes)."\n";
	}



?>
