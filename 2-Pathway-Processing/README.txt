=======================
pathway_downloader.php
=======================

The script downloads a list of pathways. Each pathway is processed and saved
in a csv file containing the list of nodes. While doing this the pathway is
processed to include transcription factors e inter/intra genic miRNA.

Pathway can be downloaded directly from KEGG in KGML format or
from http://togows.dbcls.jp. The difference is that http://togows.dbcls.jp
includes more information regarding the nodes compared to the KGML format.

Input parameters are provided through the ../config.php file

Output:

A CSV file for each downloaded pathway listing genes, TFs and miRNAs of the enhanced pathway

========================
pathway_extract_info.php
========================
Creates a summary of the pathways required for the enrichment analysis

Input parameters are provided through the ../config.php file

Output:

A csv file containing the summary information for each processed pathway
(one pathway per row)
