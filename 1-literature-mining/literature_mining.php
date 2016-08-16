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

	$querycount = 1;
	/* Start processing each query */
	foreach ($queries as $q) {

		/* Get the list of papers from NCBI */
		print ">>>>Processing query Q".$querycount.":".$q."\n";
		print ">>>>GET - List of publications from PubMED\n";
		$searchresult = file_get_contents('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&term='.$q.'&RetMax=500');

		$xml = simplexml_load_string($searchresult);
		if ($xml === false) {
		    echo "Failed loading XML: ";
		    foreach(libxml_get_errors() as $error) {
			echo "<br>", $error->message;
		    }
		} else {
			/* Get the list of genes annotated with the identified papers */
			$PaperIds= (array) $xml->IdList->Id;
			$Paperidlist = implode (",", $PaperIds);
			print "----PUBMEDIds: $Paperidlist\n";
			print ">>>>GET - List of annotated genes\n";
			$searchresult = file_get_contents('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi?dbfrom=pubmed&db=gene&id='.$Paperidlist.'&term=AND%20"Homo%20sapiens"[porgn:__txid9606]&RetMax=10000');

			$xml = simplexml_load_string($searchresult);
			if ($xml === false) {
			    echo "Failed loading XML: ";
			    foreach(libxml_get_errors() as $error) {
				echo "<br>", $error->message;
			    }
			} else {
				$genes = array();
				foreach ($xml->LinkSet->LinkSetDb[0]->Link as $l) {
					$genes[]  = "hsa:".$l->Id;
				}
				/* Get the list of pathways annotated with the identified genes */
				$genestring=implode("+", $genes);
				print "----GENEIds: $genestring\n";
				print ">>>>GET - List of annotated pathways\n";

				$homepage = file_get_contents('http://rest.genome.jp/link/pathway/'.$genestring);
				print ("----".$homepage);
				$rows = preg_split( "/[ \t\n]/", $homepage );
				$pathways[$querycount] = array();

				foreach ($rows as $r) {
					if (substr( $r, 0, 4 ) === "path") {
						$pathways[$querycount][] = $r;
					}
				}

				/* Removes duplicated pathways */
				$pathways[$querycount] = array_unique($pathways[$querycount]);

				print ">>>>GET - List of uniqe pathways\n";
				print "----";
				print_r($pathways[$querycount]);
			}
		}
		$querycount++;
	}

	/* Merges pathways identidied by the considered queries */
	print ">>>> MERGING Pathway lists\n";
	$pathwaylist = array();
	$querycount=1;
	foreach ($queries as $q) {
		$pathwaylist = array_merge ($pathwaylist,$pathways[$querycount]);
		$querycount++;
	}
	$pathwaylist = array_unique($pathwaylist);


	/* Filters the identified pathways based on BRITE and produces the output files */	
	print ">>>> PATHWAYS\n";

	$fout = fopen ("literature_mining_pathway_list.csv","w");
	fprintf ($fout,"Id;Name\n");
	foreach ($pathwaylist as $p) {
		$url = "http://rest.kegg.jp/get/".$p;
		$pinfo = file_get_contents($url );
		$rows = preg_split( "/[\n]/", $pinfo );
		$flag = false;
		foreach ($rows as $r) {
			$cols = preg_split( "/  +/", $r );
			if ($cols[0]==="ENTRY") $Entry=$cols[1];
			if ($cols[0]==="NAME") $Name=$cols[1];
			if ($cols[0]==="CLASS") {
				foreach ($briteclasses as $b) {
						if (strpos (trim($cols[1]) , $b ) !== false ) $flag=true;
				}

				$Class=trim($cols[1]);
			}
		}
		if ($flag == true) {
			print $Entry."\t".$Name."\t".$Class."\n";
			fprintf ($fout,"%s;%s\n", $Entry, $Name);
		}

	}
	fclose ($fout);

?>
