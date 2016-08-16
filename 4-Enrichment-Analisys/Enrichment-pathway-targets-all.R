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

require('CombinePValue')


# Load target data
target_data <- read.table("./results/mirna-targets-info.csv", sep=";", quote="\"", stringsAsFactors=FALSE)
pathways <- read.csv("./2-Pathway-List.csv", sep=";", dec = ".", stringsAsFactors=FALSE)
# Remove the line containing the pathway name that is not used
pathways = pathways[-2]
# Load pathway info
pathway_info_alltargets <- read.table("./results/processed-pathways/pathways-info-targets-all.csv", sep=";", quote="\"", stringsAsFactors=FALSE, header = TRUE)

# Load the list of total targets for each miRNA
mirna_total_targets <- read.csv("./0-Databases/mirna_total_targets.csv", sep=";")



# **********************************************************************************
# ***
# ***   ANALYSIS WITH MIRTARBASE
# ***
# **********************************************************************************

targetoffset = 1;

# Extract the list of columns containing the number of targets on each pathway

targets_columns = c();
for (i in 1:dim(pathways)[1]) {
  p = which (target_data == pathways$Id[i], arr.ind=TRUE);
  targets_columns <- c (targets_columns, p[2]+targetoffset); #This is the column containing the computational targets.
}

# Extract the list of rows containing the mirna to analyze
targets_rows <- seq (3,nrow(target_data))
# Creates the matrix with the data to analyze
targets_matrix <- data.matrix(target_data[targets_rows,targets_columns])
row.names(targets_matrix) <- target_data[targets_rows,1]
colnames(targets_matrix) <- pathways$Id


# Computes the mirna score as the average pathway score (since rowMeans works by column I need to transpose the matrix)
#score = rowMeans (t(t(targets_matrix) / pathway$Total))

#Begin the enrichment analysis


p_matrix = matrix(nrow=dim(targets_matrix)[1],ncol=dim(targets_matrix)[2])
row.names(p_matrix) <- target_data[targets_rows,1]
colnames(p_matrix) <- pathways$Id


for (i in 1:dim(targets_matrix)[1]) { #rows are mirna
  for (j in 1:dim(targets_matrix)[2]) { #cols are pathways

    ntarget_pathway = targets_matrix[i,j]
    ngenes_pathway = pathway_info_alltargets$Total[which (pathway_info_alltargets$Id == pathways$Id[j])]

    if (length(which (mirna_total_targets$mirnaid==target_data[i+2,1])) != 0) {
      ntarget_mirna = mirna_total_targets[which(mirna_total_targets$mirnaid==target_data[i+2,1]),targetoffset+1]
    } else {
      ntarget_mirna = 0
    }

    testor = rbind (c(ntarget_pathway,ngenes_pathway-ntarget_pathway ),
                    c(ntarget_mirna , mirna_total_targets[which(mirna_total_targets$mirnaid=="TOTAL"),targetoffset+1]-ntarget_mirna))
    f <- fisher.test(testor);
    p_matrix[i,j]=f$p.value;
  }
}

sscore = matrix(nrow=dim(targets_matrix)[1],ncol=1)
row.names(sscore) <- row.names(p_matrix)
colnames(sscore) <- c("pvalue");
for (i in 1:dim(targets_matrix)[1]) {
  s <- selfcontained.test(pvalue=p.adjust(p_matrix[i,],method="fdr"),weight=pathways$Weight,p_permu=NA)

  sscore[i]=s$`significance level for combining pvalues`
}


mirnasscore = data.frame(sscore, row.names = row.names(sscore))
mirnasscore <- mirnasscore[order(mirnasscore$pvalue), ,drop=FALSE]

significantmirnasscore = subset(mirnasscore, pvalue<0.01)

tiff("./results/mirnascore-alltargets.tif", width = 12, height = 6,  units="in", res = 300)

barplot(-log(significantmirnasscore$pvalue[seq(1,30)]),
        names.arg = as.vector(row.names(significantmirnasscore))[seq(1,30)],
        space=1, border=1,
        main="",
        ylab="-log(p-value)",
        las=3,
        #log="y",
        font.lab=2,
        cex.names=0.7)
dev.off()
write.table(mirnasscore,"./results/mirnascore-alltargets.csv",sep = ";")
