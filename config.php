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


	/* ***********************************************************************************************
	*****			 
	*****				ITERATURE MINING INPUT PARAMETERS
	*****
	*************************************************************************************************** */


	/* This array defines the set of KEGG Brite classes used to filter the pathways.
	Only pathways annotated with one of the listed classes are considered since relevant for our study.*/

	$briteclasses = array (
		"Genetic Information Processing",
		"Environmental Information Processing",
		"Cellular Processes",
		"Organismal Systems; Immune system",
		"Organismal Systems; Development",
		"Organismal Systems; Nervous system",

	  );

	/* This array defines the set of queries to perform. The fourth query includes a set of papers specified through their pubmedid that
	   are relevant for the conisdered study but are not directly returned by the first three queries. */

	$queries = array (
		"(Chronic%20lung%20rejection)%20AND%20genetics",
		"(Obliterative%20bronchiolitis)%20AND%20genetics",
		"(Obliterative%20bronchiolitis)%20AND%20(animal%20model)",
		"25068457[uid]%20OR%2021893017[uid]%20OR%2021330466[uid]%20OR%2025172912[uid]");


	/* ***********************************************************************************************
	*****			 
	*****				PATHWAY DOWNLOADER INPUT PARAMETERS
	*****
	*************************************************************************************************** */


	$pathwaydwonloadercfg = array (
		"pathwaylistfile" => "./2-Pathway-List.csv",
		"outputdir" => "./results/processed-pathways/",
		"dwonloadsite" => "KGML",
		"intrageniconly" => true
	);

	$pathwayextractinfocfg = array (
		"pathwaylistfile" => "./2-Pathway-List.csv",
		"pathwaysdir" => "./results/processed-pathways/",
	);

	/* ***********************************************************************************************
	*****			 
	*****				mimiRNA FILTERING INPUT PARAMETERS
	*****
	*************************************************************************************************** */
	
	$miRNAfiltercfg = array (
		"pathwaylistfile" => "./2-Pathway-List.csv",
		"pathwaysdir" => "./results/processed-pathways/",
		"tissues" => array (	"A549",
					"BCELL",
					"DendriticCell",
					"Granulocite",
					"lung",
					"Monocyte",
					"NKCELL")
	);

	$getmiRNAtargetscfg = array (
		"pathwaylistfile" => "./2-Pathway-List.csv",
		"pathwaysdir" => "./results/processed-pathways/",
		"mirnalistfile" => "./results/mimirna_tissue_filtered_mirna_list.csv",
	)

?>
