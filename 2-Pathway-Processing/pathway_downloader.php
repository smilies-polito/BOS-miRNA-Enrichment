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
	$pathwayList = $pathwaydwonloadercfg["pathwaylistfile"];
	$outputFolder = $pathwaydwonloadercfg["outputdir"];
	$keggParseMethod = $pathwaydwonloadercfg["dwonloadsite"];//"JSON" "KGML";   //parameters: JSON or KGML
	$onlyIntragenicMirna = $pathwaydwonloadercfg["intrageniconly"];  

// PRELOAD MIRIAD MIRNA INFO
	// C O N F I G
	$miriadSourceInter = dirname ( __FILE__ )."/../0-Databases/miriad_human_intergenic_v.2014.txt";
	$miriadSourceIntra = dirname ( __FILE__ )."/../0-Databases/miriad_human_intragenic_v.2014.txt";

	// Parse intragenic mirna
	$arrayMiriadSourceInter = miriadParse(0, 12, $miriadSourceInter);
	// Parse intragenic mirna
	$arrayMiriadSourceIntra = miriadParse(0, 11, $miriadSourceIntra);



// FOR ALL THE PATHWAYS
	// COUNT THE LINES
	$linecount = 0;
	$handle = fopen($pathwayList, "r");


	$numberofpathways = count(file($pathwayList)) - 1;
	$file = fopen($pathwayList, "r");
	$i=0;

	echo "\nElaborating Pathways...";
	// skip first line with header
	fgetcsv($file, 1000, ";");

	while (($line = fgetcsv($file, 1000, ";")) !== FALSE) {
		$i++;

		// foreach pathway
		$kegID = $line[0];
		$pathDesc = $line[1];

		echo "\n\t $i of $numberofpathways (PathwayID: $kegID)";

		if ($keggParseMethod=="JSON"){	// IF KEGG METHOD PARSE IS JSON
			// Download the list of pathway genes
			$url = 'http://togows.dbcls.jp/entry/pathway/'.$kegID.'/genes.json';
			$obj = json_decode(file_get_contents($url), true);

			// Create an array KEY[genename] VALUE[geneid]
			$geneList = array();
			$geneSymbols = "";
			foreach ($obj[0] as $key => $val) {
				$s = explode(";",$val);
			    	$geneList[$s[0]] = $key;
				$geneSymbols .=  $s[0] . "%2C+";
			}
			$geneSymbols=substr($geneSymbols, 0, -4);
		}
		else { // IF KEGG METHOD PARSE IS KGML

			// DOWNLOAD ORIGINAL KGML FROM KEGG
			$url = 'http://www.kegg.jp/kegg-bin/download?entry='.$kegID.'&format=kgml';
			$obj = file_get_contents($url);
			$xml = new SimpleXMLElement($obj);

			// PARSING THE XML
			$geneList = array();
			$geneSymbols = "";
			foreach ($xml->entry as $element){
				$elType = $element->attributes()['type'];
				if ($elType == "gene"){
					$geneID = $element->attributes()['name'];
					$geneID = explode(" ",$geneID);
					$geneSymbol = $element->graphics->attributes()['name'];
					$geneSymbol = explode(",",$geneSymbol);
					//echo $geneID[0]. "\t". $geneSymbol[0]. "\t". $elType. "\n " ;

					$geneList[$geneSymbol[0]] = str_replace("hsa:", "", $geneID[0]);
					$geneSymbols .=  $geneSymbol[0] . "%2C+";
				}
			}
		}
		// Remove the last "%2C+"
		$geneSymbols=substr($geneSymbols, 0, -4);


		// PARSE THE LIST OF TFs
		$arrayTF = array();
		$url = "http://targetmine.mizuguchilab.org/targetmine/service/template/results?name=Gene_TFSource&constraint1=Gene&op1=LOOKUP&value1=";
		$url = $url.$geneSymbols."&extra1=H.+sapiens&format=tab"; //"&size=10";

		$data = file_get_contents($url);
		$rows = explode("\n",$data);
		$s = array();
		foreach($rows as $row) {
			$fields = str_getcsv($row, "\t");
			if (count($fields)>2 && is_numeric($fields[0])) {
				$arrayTF[$fields[1]] = $fields[0]; // $data[0] = mirID; $data[12] = host
			}
		}

		// PRINTOUT
		printOUT($geneList, $arrayTF, $arrayMiriadSourceInter, $arrayMiriadSourceIntra, $onlyIntragenicMirna, $outputFolder, $kegID);
	}

	fclose($file);


	// GENERIC FUNCTION TO PARSE BOTH INTRAGENIC AND INTERGENIC MIRIAD FILE
	function miriadParse($mirnaPos, $hostPos, $sourceData){
		$arrayMiriadData = array();
		$handle = fopen($sourceData,"r");
		while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {

			// Avoid duplicated miRNA from miRIAD
			if (array_key_exists($data[$hostPos], $arrayMiriadData)) {
				$element = $arrayMiriadData[$data[$hostPos]];
				if(!in_array($data[$mirnaPos], $element)){
					$arrayMiriadData[$data[$hostPos]][] = $data[$mirnaPos]; // $data[0] = mirID; $data[12] = host
				}
			} else {
				$arrayMiriadData[$data[$hostPos]][] = $data[$mirnaPos]; // $data[0] = mirID; $data[12] = host
			}
		}
		fclose($handle);
		return $arrayMiriadData;
	}


	function printOUT($genes, $tfs, $mirnasInter, $mirnaIntra, $onlyIntragenicMirna, $outputFolder, $kegID)
	{
		$sep = "\t";

		// create output file
		$outputFile = $outputFolder.$kegID.".csv";
		if (!($fp = fopen($outputFile, 'w')))
			return;


		// print the list of genes
		fprintf($fp, "%s%s%s%s%s\n", "NAME", $sep, "ID", $sep, "TYPE") ;

		foreach($genes as $geneK => $geneV) {
			fprintf($fp, "%s%s%s%s%s\n", $geneK, $sep, $geneV, $sep, "gene");
			// print intergenic miRNA
			if (array_key_exists($geneK, $mirnaIntra)) {
				foreach ($mirnaIntra[$geneK] as $mirnaK => $mirnaV)
				fprintf($fp, "%s%s%s%s%s\n", $mirnaV, $sep, "", $sep, "intragenicMirna");

			}

			// print intragenic miRNA
			if ($onlyIntragenicMirna == 0){
				if (array_key_exists($geneK, $mirnasInter)) {
					foreach ($mirnasInter[$geneK] as $mirnaK => $mirnaV)
					fprintf($fp, "%s%s%s%s%s\n", $mirnaV, $sep, "", $sep, "intergenicMirna");

				}
			}
		}

		// print TF
		foreach($tfs as $tfK => $tfV) {
			fprintf($fp, "%s%s%s%s%s\n", $tfK, $sep, $tfV, $sep, "tf");

			// print intergenic miRNA
			if (array_key_exists($tfK, $mirnaIntra)) {
				foreach ($mirnaIntra[$tfK] as $mirnaK => $mirnaV)
					fprintf($fp, "%s%s%s%s%s\n", $mirnaV, $sep, "", $sep, "intragenicMirna");

			}

			// print intragenic miRNA
			if ($onlyIntragenicMirna == 0){
				if (array_key_exists($tfK, $mirnasInter)) {
					foreach ($mirnasInter[$tfK] as $mirnaK => $mirnaV)
					fprintf($fp, "%s%s%s%s%s\n", $mirnaV, $sep, "", $sep, "intergenicMirna");

				}
			}
		}
	}




?>
