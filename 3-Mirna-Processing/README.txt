=============================
get_filtered_mirna.php
=============================

Get a filtered list of miRNA. Filtering is performed searching all miRNA
hosted by at least one gene of the considered pathways or miRNA expressed
in a list of selected tissues based on information downloaded from mimirna.

Input parameters are provided through the ../config.php file

Output:

Prints on stdout in CSV format the list of identified miRNAs.



=============================
get_mirna_target_info.php
=============================

This script search for each filtered miRNA and for each pathway
searches for the intersection between the mirna targets and the pathway genes.

Input parameters are provided through the ../config.php file


Output:

Print on stdout in CSV format the identified mirna target information for
each miRNA and each pathway.
