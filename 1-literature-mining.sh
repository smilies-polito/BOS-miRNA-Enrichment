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


# This script is the first step of the analisys. It performs literature mining
# and produces a preliminary list of potential pathways to manually review.

php 1-literature-mining/literature_mining.php > z-logs/literature_mining.log
mv  literature_mining_pathway_list.csv results/literature_mining_pathway_list.csv
