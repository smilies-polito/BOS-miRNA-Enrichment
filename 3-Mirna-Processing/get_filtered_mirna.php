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


	$tissues = $miRNAfiltercfg["tissues"];


	/* Anayze the pathways to get the list of intragenic miRNAs */
	
	
	$pathwaylist = $miRNAfiltercfg["pathwaylistfile"];
	$pathwaydir = $miRNAfiltercfg["pathwaysdir"];
	$intragenicmiRNA = array();

	/* Open th file containing the list of pathways */
	$handle1 = fopen($pathwaylist,"r");
	/* skip the first line containing the header */
	fgetcsv($handle1, 1000, ";");

	/*process each pathway */
	while (($p = fgetcsv($handle1, 1000, ";")) !== FALSE) {
		$pathwayid = $p[0];
		$filename = $pathwaydir."/".$pathwayid.".csv";
		$handle = fopen($filename,"r");

		/* Count the number of genes and TF */
		while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
		    $symbol = $data[0];
		    $id = $data[1];
		    $type = $data[2];
		    if ($type=="intragenicMirna") {
			$intragenicmiRNA[]=$symbol;
		    }
		}
		fclose ($handle);
	}
	fclose ($handle1);
	$intragenicmiRNA = array_unique($intragenicmiRNA);

	/* Load the mimiRNA local database for the selected tissues */

	$tissuesmirnas = array();
	foreach ($tissues as $t) {

		$handle1 = fopen(dirname ( __FILE__ )."/../0-Databases/mimirna-tissues-lists/$t.csv","r");
		/* skip the first line containing the header */
		fgetcsv($handle1, 1000, ";");

		/*process each mirna */
		while (($m = fgetcsv($handle1, 1000, ";")) !== FALSE) {
			$tissuesmirnas[$t][]=$m[0];
		}
		fclose ($handle1);

	}

	$outputmirnalist = array();



	/* Process all miRNA in miRBase 21*/
	$handle2 = fopen (dirname ( __FILE__ )."/../0-Databases/miRBase_miRNA_v21.csv","r");
	/* skip the first line containing the header */
	fgetcsv($handle2, 1000, ";");

	/*
	    [0] => Accession
	    [1] => ID
	    [2] => Status
	    [3] => Sequence
	    [4] => Mature1_Acc
	    [5] => Mature1_ID
	    [6] => Mature1_Seq
	    [7] => Mature2_Acc
	    [8] => Mature2_ID
	    [9] => Mature2_Seq
	*/

	print "MirnaId;Accession;Intragenic";
	foreach ($tissues as $t) {
		print ";$t";

	}
	print "\n";

	while (($m = fgetcsv($handle2, 1000, ";")) !== FALSE) {


		if (strstr($m[1],"hsa") !== false) {
		
			$outputflag = false;	
			$outputstr = $m[1].";".$m[0];
			if (in_array($m[1], $intragenicmiRNA)) {
				$outputstr .= ";1";
				$outputflag = true;
			} else {
				$outputstr .= ";0";
			}

			foreach ($tissuesmirnas as $tm) {

				$found = false;
				foreach ($tm as $mm) {
					if ($mm[strlen($mm)-1]=="*"){ 
						$mm = substr($mm,0,strlen($mm)-1);
					}
					if (preg_match("/^".$mm."[a-z]?(-[1-9])?(-(3p|5p))?$/",$m[5])   || preg_match("/^".$mm."[a-z]?(-[1-9])?(-(3p|5p))?$/",$m[8])   ) {
						$found=true;
						$outputflag = true;
						break;
					}	
				}
				if ($found == true) $outputstr .= ";1"; else $outputstr.= ";0";			

			}
			if ($outputflag == true) print $outputstr."\n";


		}

	}
		
	fclose ($handle2);
	


?>
