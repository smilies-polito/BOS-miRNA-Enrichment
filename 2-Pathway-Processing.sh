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

# This file processes the selected list of pathways. It donwloads all pathways
# from KEGG adding TF and miRNA info and generates aggregate informatio for
# each pathway required during the next steps of the analisys.


# Download all pathways to analyze and add information about transcription factors and intragenic miRNA
php 2-Pathway-Processing/pathway_downloader.php 

# Compute pathway summary for the statistical analisys considering mirtarbase targets
php 2-Pathway-Processing/pathway_extract_info.php ./0-Databases/targets-mirtarbase-targeted-genes.json > ./results/processed-pathways/pathways-info-targets-mirtarbase.csv

# Compute pathway summary for the statistical analisys considering all targets
php 2-Pathway-Processing/pathway_extract_info.php ./0-Databases/targets-all-targeted-genes.json > ./results/processed-pathways/pathways-info-targets-all.csv
