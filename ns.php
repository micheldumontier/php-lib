<?php
/**
Copyright (C) 2012 Michel Dumontier

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

/**
 * Namespaces
 * @version 1.0
 * @author Michel Dumontier
 * @description 
*/

class CNamespace
{
  const bio2rdf_uri = "http://bio2rdf.org/";
  private $all_ns = '';
  
  private $base_ns = array(
	'xsd'  => array('name'=>'XML Schema', 'uri' => 'http://www.w3.org/2001/XMLSchema#'),
	'rdf'  => array('name'=>'RDF','uri'=>'http://www.w3.org/1999/02/22-rdf-syntax-ns#'),
	'rdfs' => array('name'=>'RDF Schema','uri'=>'http://www.w3.org/2000/01/rdf-schema#'),
	'owl'  => array('name'=>'Web Ontology Language','uri' => 'http://www.w3.org/2002/07/owl#'),
	'dc'   => array('name'=>'Dublin Core','uri' =>'http://purl.org/dc/terms/'),
	'skos' => array('name'=>'SKOS','uri'=>'http://www.w3.org/2004/02/skos/core#'),
	'foaf' => array('name'=>'FOAF','uri'=>'http://xmlns.com/foaf/0.1/'),
	'sio'  => array('name'=>'Semanticscience Integrated Ontology','uri'=>'http://semanticscience.org/resource/'),
  );

  private $ext_ns = array(
  //'id' => array('name' => '','description' => '','uri' => '', 'synonyms' => array(), 'type' => 'data','classification','part-of' => 'ns'),
	'aracyc'    => array('name' => 'Aradopsis CYC genome database','url'=>'http://www.arabidopsis.org/biocyc/'),
    'bio2rdf'   => array('name' => 'Bio2RDF', 'url'=>'http://bio2rdf.org'),
	'biopaxl2'  => array('name' => 'BioPAX level 2','url'=>'http://www.biopax.org'),
	'biopaxl3'  => array('name' => 'BioPAX level 3','url'=>'http://www.biopax.org'),
	'brenda'    => array('name' => 'BRENDA Enzyme database', 'url'=>'http://www.brenda-enzymes.info/'),
	'ahfs'		=> array('name' => 'Pharmacologic-Therapeutic Classification System', 'url'=> 'http://www.ahfsdruginformation.com/class/index.aspx', 'type' => 'classification'),
	'afcs'      => array('name' => 'UCSD Signaling Gateway','url'=>'http://www.signaling-gateway.org/'),
	'apo'       => array('name' => 'Ascomycetes Phenotype Ontology', 'url'=>'http://purl.bioontology.org/ontology/APO','type'=>'classification'),
	'atc'       => array('name' => 'Anatomical Therapeutic Chemical Classification', 'url'=>'http://www.whocc.no/atc_ddd_index/','type' => 'classification'),
	'bind'      => array('name' => 'Biomolecular Interaction Database'),
	'bindingdb' => array('name' => 'BindingDB','url'=>'http://www.bindingdb.org'),
	'biogrid'   => array('name' => 'BioGrid Interaction Database','url'=>'http://thebiogrid.org/', 'synonyms'=> array('grid')),
	'cabri'     => array('name' => 'Common Access to Biotechnological Resources and Information', 'description' => 'an online service where users can search a number of European Biological Resource Centre catalogues', 'url'=> 'http://www.cabri.org/'),
	'candida'   => array('name' => 'Candida Genome Database','url'=>'http://www.candidagenome.org/'),
	'cas'       => array('name' => 'Chemical Abstracts Service','url'=>'http://www.cas.org/','synonyms'=>array('CHEMICALABSTRACTS')),
	'chebi'     => array('name' => 'Chemical Entities of Biological Interest','part-of'=>'ebi','url'=>'http://www.ebi.ac.uk/chebi/'),
	'chemspider' => array('name' => 'ChemSpider','url'=>'http://www.chemspider.com/'),
	'coil'      => array('name' => 'Database of parallel two-stranded coiled-coils','url'=>'http://www.ch.embnet.org/software/coils/COILS_doc.html'),
	'corum'     => array('name' => 'Comprehensive Resource of Mammalian protein Complexes', 'url'=>'http://mips.helmholtz-muenchen.de/genre/proj/corum/'),
	'cpath'     => array('name' => 'CPATH - pathwaycommons resources'),
	'ctd'       => array('name' => 'Comparative Toxicogenomics Database','url'=>'http://ctdbase.org/'),
	'cygd'      => array('name' => 'MIPS Saccharomyces cerevisiae genome database','url'=>'http://mips.helmholtz-muenchen.de/genre/proj/yeast/'),
	'dbsnp'     => array('name' => 'dbSNP short genetic variation database','part-of'=>'ncbi','url'=>'http://www.ncbi.nlm.nih.gov/projects/SNP/'),
	'dip'       => array('name' => 'Database of Interacting Proteins','url'=>'http://dip.doe-mbi.ucla.edu/dip/Main.cgi'),
	'ddbj'      => array('name' => 'DDBJ sequence database'),
	'dpd'		=> array('name' => 'Health Canada Drug Product Database','url'=>'http://www.hc-sc.gc.ca/dhp-mps/prodpharma/databasdon/index-eng.php'),
	'drugbank'  => array('name' => 'DrugBank','url'=>'http://drugbank.ca'),
	'drugbank_target'  => array('name' => 'DrugBank targets'),
	'ec'        => array('name' => 'Enzyme Classification'),
	'embl'      => array('name' => 'EMBL sequence database'),
	'ensembl'   => array('name' => 'EnsEMBL genomic database'),
	'eco'       => array('name' => 'Evidence Code Ontology','type'=>'classification'),
	'ecocyc'    => array('name' => 'E.coli CYC database'),
	'ensembl'   => array('name' => 'ENSEMBL'),
	'euroscarf' => array('name' => 'European Saccharomyces Cerevisiae Archive for Functional Analysis', 'url' => 'http://web.uni-frankfurt.de/fb15/mikro/euroscarf/'),
	'flybase'   => array('name' => 'FlyBase','url'=>'http://flybase.org/'),
	'fprintscan' => array('name' => ''),
	'kegg'      => array('name' => 'KEGG','synonyms' => array('compound', 'KEGG Compound','KEGG Drug')),
	'genatlas'	=> array('name' => 'GenAtlas'),
	'genbank'	=> array('name' => 'GenBank'),
	'gi'        => array('name' => 'NCBI GI'),
	'genecards'	=> array('name' => 'GeneCards'),
	'gene3d'    => array('name' => 'Gene3D','url'=>'http://gene3d.biochem.ucl.ac.uk/Gene3D/'),
	'geneid'    => array('name' => 'NCBI Gene', 'synonyms' => array('geneid','entrez gene','ENTREZ_GENE','ENTREZGENE/LOCUSLINK'), 'url'=>'http://www.ncbi.nlm.nih.gov/gene/'),
	'germonline' => array('name' => 'GermOnline','url'=>'http://www.germonline.org'),
	'go'        => array('name' => 'Gene Ontology'),
	'gp'        => array('name' => 'NCBI Genome database','part-of'=>'ncbi'),
	'gtp'		=> array('name' => 'Guide to Pharmacology'),
	'het'       => array('name' => 'PDB heteratom vocabulary', 'url'=>'http://www.ebi.ac.uk/pdbsum/'),
	'hprd'      => array('name' => 'Human Protein Reference Database'),
	'hgnc'		=> array('name' => 'HUGO Gene Nomenclature Committee (HGNC)'),
	'hp'        => array('name' => 'Human Phenotype Ontology (HPO)'),
	'humancyc'  => array('name' => 'Human CYC database'),
	'icd9'      => array('name' => 'International Classification of Disease v9'),
	'icd10'     => array('name' => 'International Classification of Disease v10'),
	'innatedb'  => array('name' => ''),
	'intact'    => array('name' => 'Intact Interaction Database'),
	'interpro'  => array('name' => 'InterPro', 'url'=>'http://www.ebi.ac.uk/interpro/'),
	'ipi'       => array('name' => 'International Protein Index'),
	'irefindex'         => array('name' => 'iRefIndex'),
	'irefindex_rogid'   => array('name' => 'iRefIndex','part-of' => 'irefindex'),
	'irefindex_rigid'   => array('name' => 'iRefIndex','part-of' => 'irefindex'),
	'irefindex_irigid'  => array('name' => 'iRefIndex','part-of' => 'irefindex'),
	'irefindex_crigid'  => array('name' => 'iRefIndex','part-of' => 'irefindex'),
	'irefindex_crogid'  => array('name' => 'iRefIndex','part-of' => 'irefindex'),
	'irefindex_icrogid' => array('name' => 'iRefIndex','part-of' => 'irefindex'),
	'irefindex_icrigid' => array('name' => 'iRefIndex','part-of' => 'irefindex'),
	'iuphar'		=> array('name' => 'iuphar'),
	'iupharligand' => array('name' => '','part-of'=>'iuphar'),
	'knapsack' => array('name' => 'KNApSAcK: A Comprehensive Species-Metabolite Relationship Database','url'=>'http://kanaya.naist.jp/KNApSAcK/'),
	'matrixdb'  => array('name' => ''),
	'mesh' => array('name' => 'Medical Subject Headings'),
	'metacyc' => array('name' => 'Encyclopedia of Metabolic Pathways'),
	'mgi' => array('name'=>'Mouse Genome Informatics'),
	'mi' => array('name' => ''),
	'mint' => array('name' => 'Molecular INTeraction database'),
	'mips' => array('name' => ''),
	'mpact' => array('name' => ''),
	'mpi' => array('name' => ''),
	'ncbi' => array('name' => 'National Center for Biotechnology Information'),
	'ncit' => array('name' => 'National Cancer Institute Thesaurus'),
	'ndc' => array('name' => 'National Drug Code Directory'),
	'newt' => array('name' => 'UniProt taxonomy', 'url'=>'http://www.uniprot.org/help/taxonomy'),
	'obo' => array('name' => 'The Open Biological and Biomedical Ontologies'),
	'offsides' => array('name' => 'Off-label side effects','url'=>'http://pharmgkb.org'),
	'omim' => array('name' => 'Online Mendelian Inheritance in Man'),
	'ophid' => array('name' => 'Online predicted human interaction database'),
	'orphanet'=>array('name'=> 'Orphanet : The portal for rare diseases and orphan drugs'),
	'ordr'=> array('name'=>'Office of Rare Disease Research'),
	'patternscan' => array('name' => ''),
	'pato' => array('name' => 'Phenotypic Quality Ontology'),
	'panther' => array('name' => 'The PANTHER (Protein ANalysis THrough Evolutionary Relationships) Classification System'),
	'pdb'=> array('name' => 'Protein Databank'),
	'pfam' => array('name' => 'Protein Families'),
	'pharmgkb' => array('name' => 'PharmGKB knowledge base'),
	'pir'=> array('name' => 'Protein Information Resource'),
	'prf'=> array('name' => 'Protein Research Foundation'),
	'prodom'=> array('name' => 'Protein Domain Families'),
	'profilescan'=> array('name' => ''),
	'pubmed'=> array('name' => 'PubMed'),
	'pmc'=>array('name'=>'PubMed Central'),
	'psi-mi'=>array('name'=>'Protein Standards Initiative - Molecular Interactions'),
	'psi-mod'=>array('name'=>'Protein Standards Initiative - Modifications'),
	'pubchemcompound'=> array('name' => '', 'synonyms' => array('PubChem Compound')),
	'pubchemsubstance'=> array('name' => '', 'synonyms' => array('PubChem Substance')),
	'reactome'=> array('name' => 'REACTOME'),
	'refseq' => array('name' => '','part-of' => 'ncbi'),
	'registry'=> array('name' => 'Bio2RDF Namespace Registry'),
	'registry_dataset'=> array('name' => 'Bio2RDF Dataset Registry'),
	'resid' => array('name' => 'RESID database of protein modifications','url'=>'http://www.ebi.ac.uk/RESID/'),
	'seg'=> array('name' => ''),
	'sgd'=> array('name' => 'Saccharomyces Genome Database'),
	'smart'=> array('name' => 'SMART'),
	'snomed'=> array('name' => 'Systematized Nomenclature of Medicine'),
	'so'=> array('name' => 'Sequence Ontology'),
	'superfamily'=> array('name' => ''),
	'swissprot'=> array('name' => 'SwissProt', 'part-of' => 'uniprot'),
	'symbol' => array('name' => 'Gene Symbols'),
	'taxon'=> array('name' => 'NCBI Taxonomy','synonyms'=>array('taxon','ncbitaxon'),'part-of' => 'irefindex'),
	'tcdb'=> array('name' => 'Transporter Classification Database'),
	'tigr'=> array('name' => 'TIGR'),
	'tpg'=> array('name' => ''),
	'trembl'=> array('name' => 'TrEMBL'),
	'ttd'=>array('name'=>'Therapeutic Targets Database', 'url'=>'http://bidd.nus.edu.sg/group/ttd/'),
	'twosides'=>array('name'=>'Drug-Drug Associations','url'=>'http://pharmgkb.org'),
	'umls'=> array('name' => 'UMLS'),
	'umbbd'=> array('name' => 'umbbd biocatalysis/biodegredation database', 'url'=>'http://umbbd.ethz.ch/'),
	'unigene'=> array('name'=>'UniGene'),
	'uniparc'=> array('name' => 'UniParc','part-of' => 'uniprot'),
	'uniprot'=> array('name' => 'UniProt','part-of' => 'uniprot'),
	'uniref'=> array('name' => 'UniRef','part-of' => 'uniprot'),
	'unists'=> array('name' => 'UniSTS', 'url' => 'http://www.ncbi.nlm.nih.gov/unists/'),
	'uo'=> array('name' => 'Unit Ontology'),
	'wormbase' => array('name'=>'WormBase'),
	'zfin'=>array('name'=>'Zebrafish')
	);
	
	function __construct()
	{
		foreach($this->ext_ns AS $ns => $obj) {
			$this->ext_ns[$ns]['uri'] =  self::bio2rdf_uri.$ns.':';
			$this->ext_ns[$ns.'_vocabulary']['uri'] =  self::bio2rdf_uri.$ns.'_vocabulary:';
			$this->ext_ns[$ns.'_resource']['uri'] =  self::bio2rdf_uri.$ns.'_resource:';
		}
		$this->all_ns = array_merge($this->base_ns,$this->ext_ns);		
   }
   
	function isNS($ns)
	{
		if(!isset($this->all_ns[$ns])) return false;
		return TRUE;
	}
	
	function getNSURI($ns)
	{
		if($this->isNS($ns)) {
			if($this->all_ns[$ns]['uri']) return $this->all_ns[$ns]['uri'];
		}
		return FALSE;
	}
	
	function ParsePrefixedName($prefixed_name,&$ns,&$id, $delimiter = ':')
	{
		$a = explode($delimiter,$prefixed_name,2);
		if(count($a) == 1) {
			// there is no prefix
			$ns = null;
			$id = $prefixed_name;
		} else {
			$ns = strtolower($a[0]);
			$id = $a[1];
		}
		return TRUE;
	}
	
	function getFQURI($prefixed_name) 
	{
		$this->ParsePrefixedName($prefixed_name,$ns,$id);		
		if(!$this->isNS($ns)) {trigger_error("Invalid qname ".$ns. " for $prefixed_name", E_USER_ERROR); exit;}
		return $this->getNSURI($ns).$id;
	}
	
	function GetTTLPrefix($ns)
	{
		 return '@prefix '.$ns.': <'.$this->getNSURI($ns).'> .'.PHP_EOL;
	}
	
	function GenerateTTLHeader()
	{
		$buf = '';
		foreach($this->all_ns AS $ns => $obj) {
			$buf .= $this->GetTTLPrefix($ns);
		}
		return $buf;
	}
}
