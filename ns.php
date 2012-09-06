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
 * @description Class that contains a namespace registry and related functions
*/
class CNamespace
{
  const base_uri = "http://bio2rdf.org/";
  private $all_ns = '';
  private $ns_map = null;
  
  private $terminologies = array(
	'xsd'  => array('name'=>'XML Schema',           'uri' => 'http://www.w3.org/2001/XMLSchema#'),
	'rdf'  => array('name'=>'RDF',                  'uri'=>'http://www.w3.org/1999/02/22-rdf-syntax-ns#'),
	'rdfs' => array('name'=>'RDF Schema',           'uri'=>'http://www.w3.org/2000/01/rdf-schema#'),
	'owl'  => array('name'=>'Web Ontology Language','uri' => 'http://www.w3.org/2002/07/owl#'),

	//
	'id-validation-regexp' => array('name' => 'Regular Expression for identifier syntax'),
	'search-url' => array('name' => 'Pattern for placing an identifier into a URI'),
	
	// terminologies, ontologies
	'biopax'    => array('name' => 'synthetic BioPAX vocabulary','url'=>'http://www.biopax.org'),
	'biopaxl2'  => array('name' => 'BioPAX level 2','uri'=>'http://www.biopax.org/release/biopax-level2.owl#','url'=>'http://www.biopax.org'),
	'biopaxl3'  => array('name' => 'BioPAX level 3','uri'=>'http://www.biopax.org/release/biopax-level3.owl#','url'=>'http://www.biopax.org'),
	'dc'   => array('name'=>'Dublin Core Metatdata','uri' =>'http://purl.org/dc/terms/'),
	'foaf' => array('name'=>'Friend of a Friend (FOAF)','uri'=>'http://xmlns.com/foaf/0.1/'),
	'prov' => array('name'=>'Provenance Ontology (PROV)','uri'=>'http://www.w3.org/ns/prov#'),
	'sio'  => array('name'=>'Semanticscience Integrated Ontology (SIO)','uri'=>'http://semanticscience.org/resource/'),
	'skos' => array('name'=>'Simple Knowledge Organization System (SKOS)','uri'=>'http://www.w3.org/2004/02/skos/core#'),
	'mesh' => array('name' => 'Medical Subject Headings'),
	'icd9' => array('name' => 'International Classification of Disease v9'),
	'icd10'=> array('name' => 'International Classification of Disease v10'),
	'snomed'=> array('name' => 'Systematized Nomenclature of Medicine'),
	'umls' => array('name' => 'UMLS'),
	'void' => array('name'=>'Vocabulary of Interlinked Datasets (VOID)','uri'=>'http://rdfs.org/ns/void#')
	
	);
	
	// obo
	private $obo = array(
	'aeo' => array('name'=>'Anatomical Entity ontology'),
	'ahfs' => array('name' => 'Pharmacologic-Therapeutic Classification System', 'url'=> 'http://www.ahfsdruginformation.com/class/index.aspx', 'type' => 'classification'),
	'apo' => array('name' => 'Ascomycetes Phenotype Ontology', 'url'=>'http://purl.bioontology.org/ontology/APO','type'=>'classification'),
	'atc' => array('name' => 'Anatomical Therapeutic Chemical Classification', 'url'=>'http://www.whocc.no/atc_ddd_index/','type' => 'classification'),
	'atm' => array('name'=>'African Traditional Medicine Ontology'),
	'biositemap' => array('name'=>'BioSiteMap', 'uri'=>'http://bioontology.org/ontologies/biositemap.owl#'),
	'bto' => array('name' => 'BRENDA tissue ontology','synonyms'=>'brendatissueontology'),
	'cco' => array('name' => 'Cell cycle ontology','synonyms'=>'cell cycle ontology'),
	'chebi' => array('name' => 'Chemical Entities of Biological Interest','part-of'=>'ebi','url'=>'http://www.ebi.ac.uk/chebi/'),	
	'clo' => array('name' => 'Cell line ontology','synonyms'=>'cl'),
	'cto' => array('name' => 'Cell type ontology','synonyms'=>'cell type ontology'),
	'do' => array('name' => 'Human Disease Ontology','synonyms'=>array('human disease ontology','doid')),
	'eco'       => array('name' => 'Evidence Code Ontology','synonyms'=>'evidence codes ontology'),
	'fbdv' => array('name'=>'Drosophila Development Ontology'),
	'fbdv_root' => array('name'=>'Drosophila Development Root Ontology'),
	'fma' => array('name'=>'Foundational Model of Anatomy'),
	'go'        => array('name' => 'Gene Ontology','synonyms'=>array('gene_ontology','gene ontology')),
	'hp'        => array('name' => 'Human Phenotype Ontology (HPO)'),
	'lsm' => array('name'=>'Leukocyte surface markers ontology'),
	'obi' => array('name'=>'Ontology for biomedical investigation'),
	'pato' => array('name'=>'Phenotype and Trait Ontology'),
	'psi-mi'=>array(
		'name'=>'Protein Standards Initiative - Molecular Interactions',
		'synonyms'=> array('mi','obo.mi'),
		'identifiers.org'=>'obo.psi-mi'
		),
	'psi-mod'=>array(
		'name'=>'Protein Standards Initiative - Modifications',
		'synonyms'=>array('protein modification ontology','obo.psi-mod','mod'),
		'identifiers.org'=>'obo.psi-mod'),
	'ma' => array('name'=>'mouse anatomy ontology'),
	'nif_subcellular'=>array('name'=>'Neuroinformatics subcellular ontology'),
	'pr' => array('name'=>'Protein Ontology'),
	'sbo' => array('name'=>'Systems Biology Ontology','synonyms'=>'Systems_Biology_Ontology'),
	'span' => array('name'=>'Basic Formal Ontology SPAN'),
	'snap' => array('name'=>'Basic Formal Ontology SNAP'),
	'so'=> array('name' => 'Sequence Ontology'),	
	'tads' => array('name'=>'tick gross anatomy ontology'),
	'taxon'=> array(
		'name' => 'NCBI Taxonomy',
		'synonyms'=>array('taxid','ncbitaxon','ncbitaxonomy','ncbi_taxonomy','taxonomy'),
		'identifiers.org'=>'taxon'),
	'uo'=> array('name' => 'Unit Ontology')
  );

  private $datasets = array(
  //'id' => array('name' => '','description' => '','uri' => '', 'url'=>'', 'synonyms' => array(), 'type' => 'dataset','terminology','part-of' => 'ns'),
	'3dmet'	=> array('name'=>'3Dmet'),
	'afcs'      => array('name' => 'UCSD Signaling Gateway','url'=>'http://www.signaling-gateway.org/'),
	'alfred'	=> array('name' => 'Allele Frequency Database','url'=>'http://alfred.med.yale.edu/'),
	'ath' => array(
		'name' => 'Arabidopsis Hormone Database',
		'url' => 'http://ahd.cbi.pku.edu.cn/'),
	'aracyc'    => array('name' => 'Aradopsis CYC genome database','url'=>'http://www.arabidopsis.org/biocyc/'),
	'beilstein' => array('name'=>'Beilstein Registry Number for organic compounds'),
	'biocyc'    => array('name' => 'CYC genome database'),
	'bio2rdf'   => array('name' => 'Bio2RDF', 'url'=>'http://bio2rdf.org'),
	'bio2rdf_dataset' => array('name' => 'Bio2RDF datasets'), // for provenance
	'biomodels' => array(
		'name' => 'Biomodels database', 
		'identifiers.org'=>'biomodels.db',
		'synonyms'=>array('biomodelsdatabase','biomodels.db')),
	'bind'      => array('name' => 'Biomolecular Interaction Database','synonyms'=>'bind_translation'),
	'bindingdb' => array('name' => 'BindingDB','url'=>'http://www.bindingdb.org'),
	'biogrid'   => array('name' => 'BioGrid Interaction Database','url'=>'http://thebiogrid.org/', 'synonyms'=> array('grid')),
	'brenda'    => array('name' => 'BRENDA Enzyme database', 'url'=>'http://www.brenda-enzymes.info/'),
	'cabri'     => array('name' => 'Common Access to Biotechnological Resources and Information', 'description' => 'an online service where users can search a number of European Biological Resource Centre catalogues', 'url'=> 'http://www.cabri.org/'),
	'camjedb'   => array('name' => 'Camjedb is a comprehensive database for information on the genome of Campylobacter jejuni','url'=> 'http://www.sanger.ac.uk/Projects/C_jejuni/'),
	'candida'   => array('name' => 'Candida Genome Database','url'=>'http://www.candidagenome.org/'),
	'cas'       => array('name' => 'Chemical Abstracts Service','url'=>'http://www.cas.org/','synonyms'=>array('chemicalabstracts')),
	'chembl'    => array('name' => 'ChEMBL compound bioassay data'),
	'chemblcompound' => array('name' => 'ChEMBL compound','synonyms'=>'chembl.compound'),
	'chemidplus' => array('name'=>'chemidplus identifier for chemical compounds'),
	'chemspider' => array('name' => 'ChemSpider','url'=>'http://www.chemspider.com/'),
	'coil'      => array('name' => 'Database of parallel two-stranded coiled-coils','url'=>'http://www.ch.embnet.org/software/coils/COILS_doc.html'),
	'corum'     => array('name' => 'Comprehensive Resource of Mammalian protein Complexes', 'url'=>'http://mips.helmholtz-muenchen.de/genre/proj/corum/'),
	'cpath'     => array('name' => 'CPATH - pathwaycommons resources'),
	'ctd'       => array('name' => 'Comparative Toxicogenomics Database','url'=>'http://ctdbase.org/'),
	'cygd'      => array('name' => 'MIPS Saccharomyces cerevisiae genome database','url'=>'http://mips.helmholtz-muenchen.de/genre/proj/yeast/'),
	'dailymed'  => array('name' => 'DailyMed Current Medication Information', 'url' => 'http://dailymed.nlm.nih.gov/'),
	'dbsnp'     => array('name' => 'dbSNP short genetic variation database','part-of'=>'ncbi','url'=>'http://www.ncbi.nlm.nih.gov/projects/SNP/'),
	'dip'       => array('name' => 'Database of Interacting Proteins','url'=>'http://dip.doe-mbi.ucla.edu/dip/Main.cgi'),
	'ddbj'      => array('name' => 'DDBJ sequence database','synonyms'=>'dbj'),
	'doi' => array(
		'name'=>'Digital Object Identifier',
		'identifiers.org'=>'doi'),
	'dpd'		=> array('name' => 'Health Canada Drug Product Database','url'=>'http://www.hc-sc.gc.ca/dhp-mps/prodpharma/databasdon/index-eng.php'),
	'drugbank'  => array('name' => 'DrugBank','url'=>'http://drugbank.ca'),
	'drugbank_target'  => array('name' => 'DrugBank targets'),
	'ec' => array(
		'name' => 'Enzyme Classification', 
		'synonyms'=>array('enzymeconsortrium','enzyme consortium','enzyme nomenclature','ec-code','ecnumber'),
		'identifiers.org'=>'ec-code'),
	'embl'      => array('name' => 'EMBL sequence database','synonyms'=>'emb'),
	'ensembl'   => array(
		'name' => 'EnsEMBL genomic database',
		'identifiers.org'=>'ensembl'),
	'ensemblgenomes' => array('name' => 'EnsEMBL genomes'),
	'ecocyc'    => array('name' => 'E.coli CYC database'),
	'ensembl'   => array('name' => 'ENSEMBL'),
	'euroscarf' => array('name' => 'European Saccharomyces Cerevisiae Archive for Functional Analysis', 'url' => 'http://web.uni-frankfurt.de/fb15/mikro/euroscarf/'),
	'flybase'   => array('name' => 'FlyBase','url'=>'http://flybase.org/'),
	'fprintscan' => array('name' => ''),
	'genatlas'	=> array('name' => 'GenAtlas'),
	'genbank'	=> array('name' => 'GenBank','synonyms'=>array('genbank_nucl_gi','genbank_protein_gi','gb')),
	'gi'        => array('name' => 'NCBI GI','synonyms'=>'genbank indentifier'),
	'genecards'	=> array('name' => 'GeneCards - human gene compendium','url'=>'http://www.genecards.org'),
	'gene3d'    => array('name' => 'Gene3D','url'=>'http://gene3d.biochem.ucl.ac.uk/Gene3D/'),
	'geneid'    => array('name' => 'NCBI Gene', 'synonyms' => array('ncbigene','entrez gene','ENTREZ_GENE','ENTREZGENE/LOCUSLINK','entrez gene/locuslink'), 'url'=>'http://www.ncbi.nlm.nih.gov/gene/'),
	'germonline' => array('name' => 'GermOnline','url'=>'http://www.germonline.org'),
	'gmelin' => array('name'=>'German handbook/encyclopedia of inorganic compounds initiated by Leopold Gmelin'),
	'gp'        => array('name' => 'NCBI Genome database','part-of'=>'ncbi'),
	'gtp'		=> array('name' => 'Guide to Pharmacology'),
	'hamap'		=> array('name'=>'hapmap project'),
	'het'       => array('name' => 'PDB heteratom vocabulary', 'url'=>'http://www.ebi.ac.uk/pdbsum/'),
	'hprd'      => array('name' => 'Human Protein Reference Database'),
	'hgnc'		=> array('name' => 'HUGO Gene Nomenclature Committee (HGNC)'),
	'homologene' => array('name' => 'homologene'),
	'huge'		=> array(
		'name' => 'Database of Human Unidentified Gene-Encoded Large Proteins Analyzed',
		'url'=>'http://www.kazusa.or.jp/huge/'),
	'humancyc'  => array('name' => 'Human CYC database'),	
	'innatedb'  => array('name' => ''),
	'intact'    => array(
		'name' => 'Intact Interaction Database',
		'identifiers.org'=>'intact'),
	'interpro'  => array(
		'name' => 'InterPro', 
		'url'=>'http://www.ebi.ac.uk/interpro/',
		'identifiers.org'=>'interpro'),
	'insdc'     => array('ddbj/embl/genbank', 'synonyms'=>'"ddbj/embl/genbank'),
	'ipi'       => array('name' => 'International Protein Index'),
	'iproclass' => array('name' => 'iProClass Protein Information Resource'),
	'irefindex'         => array('name' => 'iRefIndex'),
	'irefindex_rogid'   => array('name' => 'iRefIndex','part-of' => 'irefindex','synonyms'=>'rogid'),
	'irefindex_irogid'  => array('name' => 'iRefIndex','part-of' => 'irefindex','synonyms'=>'irogid'),
	'irefindex_rigid'   => array('name' => 'iRefIndex','part-of' => 'irefindex','synonyms'=>'rigid'),
	'irefindex_irigid'  => array('name' => 'iRefIndex','part-of' => 'irefindex','synonyms'=>'irigid'),
	'irefindex_crigid'  => array('name' => 'iRefIndex','part-of' => 'irefindex','synonyms'=>'crigid'),
	'irefindex_crogid'  => array('name' => 'iRefIndex','part-of' => 'irefindex','synonyms'=>'crogid'),
	'irefindex_icrogid' => array('name' => 'iRefIndex','part-of' => 'irefindex','synonyms'=>'icrogid'),
	'irefindex_icrigid' => array('name' => 'iRefIndex','part-of' => 'irefindex','synonyms'=>'icrigid'),
	'isbn' => array('name'=>'International standard book number'),
	'iuphar'		=> array('name' => 'iuphar'),
	'iupharreceptor' => array('name' => 'iuphar receptor', 'part-of' => 'iuphar'),
	'iupharligand' => array('name' => '','part-of'=>'iuphar'),
	'kegg' => array(
		'name' => 'KEGG',
		'synonyms' => array('compound','kegg.orthology','kegg.genes', 'KEGG Compound','KEGG Drug','kegg legacy','kegg pathway','kegg reaction','kegg:ecj')),
	'kegg:hsa' => array('synonyms'=>'hsa'),
	'knapsack' => array('name' => 'KNApSAcK: A Comprehensive Species-Metabolite Relationship Database','url'=>'http://kanaya.naist.jp/KNApSAcK/'),
	'lipidmaps' => array('name'=>'LIPIDMAPS database of lipds'),
	'maizegdb' => array('name'=>''),
	'matrixdb'  => array('name' => ''),
	'metacyc' => array('name' => 'Encyclopedia of Metabolic Pathways'),
	'mgi' => array('name'=>'Mouse Genome Informatics'),
	'mint' => array('name' => 'Molecular INTeraction database'),
	'mips' => array('name' => '','synonyms'=>'mppi'),
	'mirbase' => array('name'=> ''),
	'modbase' => array('name' => 'ModBase: Database of Comparative Protein Structure Models','url'=>'http://modbase.compbio.ucsf.edu/'),
	'mpact' => array('name' => ''),
	'mpi' => array('name' => '','synonyms'=>array('mpilit','mpiimex')),
	'mutdb' => array('name'=> 'MutDB contains annotations on human variation','url'=>'http://mutdb.org/'),
	'narcis' => array(
		'name' => 'NARCIS gateway to scholarly information in The Netherlands',
		'url'=>'http://www.narcis.nl/',
		'synonyms'=>'oai',
		'identifiers.org'=>'narcis'),
	'ncbi' => array('name' => 'National Center for Biotechnology Information'),
	'ncbo' => array('name' => 'National Center for Biomedical Ontology','url'=>'http://www.bioontology.org/'),
	'ncit' => array('name' => 'National Cancer Institute Thesaurus','synonyms'=>'nci'),
	'ndc' => array('name' => 'National Drug Code Directory'),
	'newt' => array('name' => 'UniProt taxonomy', 'url'=>'http://www.uniprot.org/help/taxonomy'),
	'nistchemistrywebbook' => array('name'=>'nist chemistry webbook'),
	'offsides' => array('name' => 'Off-label side effects','url'=>'http://pharmgkb.org'),
	'omim' => array(
		'name' => 'Online Mendelian Inheritance in Man',
		'identifiers.org'=>'omim'),
	'ophid' => array('name' => 'Online predicted human interaction database'),
	'orphanet'=>array('name'=> 'Orphanet : The portal for rare diseases and orphan drugs'),
	'ordr'=> array('name'=>'Office of Rare Disease Research'),
	'patternscan' => array('name' => ''),
	'panther' => array('name' => 'The PANTHER (Protein ANalysis THrough Evolutionary Relationships) Classification System'),
	'pdb'=> array('name' => 'Protein Databank','synonyms'=>array('wwpdb','pdbe','rcsb pdb')),
	'peroxibase' => array('name' => 'Peroxidase database','url'=>'http://peroxibase.isb-sib.ch/'),
	'pfam' => array('name' => 'Protein Families'),
	'pharmgkb' => array('name' => 'PharmGKB knowledge base'),
	'pir'=> array('name' => 'Protein Information Resource'),
	'pirsf' => array(
		'name' => 'Protein Information Resource SuperFamily',
		'url'=>'http://pir.georgetown.edu/pirsf/',
		'identifiers.org'=>'pirsf'),
	'prf'=> array('name' => 'Protein Research Foundation'),
	'pride'=>array('name'=> 'PRIDE'),
	'prodom'=> array('name' => 'Protein Domain Families'),
	'profilescan'=> array('name' => ''),
	'pubmed'=> array(
		'name' => 'PubMed',
		'identifiers.org'=>'pubmed'),
	'pmc'=>array('name'=>'PubMed Central'),
	'pubchemcompound'=> array('name' => '', 'synonyms' => array('PubChem Compound')),
	'pubchemsubstance'=> array('name' => '', 'synonyms' => array('PubChem Substance')),
	'pubchembioactivity'=> array('name' => '', 'synonyms' => array('PubChem Bioactivity')),
	'reactome'=> array(
		'name' => 'REACTOME',
		'synonyms'=>array('reactome database identifier'),
		'identifiers.org'=>'reactome'),
	'refseq' => array('name' => 'NCBI Reference Sequence Database (RefSeq)','part-of' => 'ncbi','synonyms'=>'ref_seq'),
	'registry'=> array('name' => 'Bio2RDF Namespace Registry'),
	'registry_dataset'=> array('name' => 'Bio2RDF Dataset Registry'),
	'resid' => array('name' => 'RESID database of protein modifications','url'=>'http://www.ebi.ac.uk/RESID/'),
	'sabiork' => array('name' => 'SABIO-RK database of biochemical reaction','uri'=>'http://sabio.h-its.org/biopax#'),
	'sabiorkcompound' => array('name'=>'SABIO-RK compounds','synonyms'=>'SABIO-RK Compound'),
	'sbpax' => array('name' => 'Systems Biology & BioPAX','uri'=>'http://vcell.org/sbpax3#'),
	'seg'=> array('name' => ''),
	'sgd'=> array('name' => 'Saccharomyces Genome Database'),
	'smart'=> array('name' => 'SMART'),
	'superfamily'=> array('name' => ''),
	'swissprot'=> array('name' => 'SwissProt', 'part-of' => 'uniprot'),
	'symbol' => array('name' => 'Gene Symbols'),
	'tair' => array('name' => 'The Arabidopsis Information Resource'),
	'tcdb'=> array('name' => 'Transporter Classification Database'),
	'tigr'=> array('name' => 'TIGR'),
	'tpg'=> array('name' => ''),
	'trembl'=> array('name' => 'TrEMBL'),
	'ttd'=>array('name'=>'Therapeutic Targets Database', 'url'=>'http://bidd.nus.edu.sg/group/ttd/'),
	'twosides'=>array('name'=>'Drug-Drug Associations','url'=>'http://pharmgkb.org'),
	'ucsc' => array('name' => 'UCSC Genome Browser', 'url'=>'http://genome.ucsc.edu/'),
	'umbbd'=> array('name' => 'umbbd biocatalysis/biodegredation database', 'url'=>'http://umbbd.ethz.ch/', 'synonyms'=>'umbbd-compounds'),
	'unigene'=> array('name'=>'UniGene'),
	'uniparc'=> array('name' => 'UniParc','part-of' => 'uniprot'),
	'uniprot'=> array(
		'name' => 'UniProt',
		'part-of' => 'uniprot', 
		'synonyms'=>array('uniprotkb','uniprotkb/trembl','swiss-prot','sp','uniprot knowledge base'),
		'identifiers.org'=>'uniprot'),
	'uniprotkb_var' => array('name'=>'UniProt variant'),
	'uniref'=> array('name' => 'UniRef','part-of' => 'uniprot'),
	'unists'=> array('name' => 'UniSTS', 'url' => 'http://www.ncbi.nlm.nih.gov/unists/'),
	'unigene'=> array('name' => 'UniGene', 'url' => 'http://www.ncbi.nlm.nih.gov/unigene/'),
	'uspatent'=>array('name'=> 'US Patent'),
	'vectorbase' => array('name' => ''),
	'vega'=> array('name' => 'The Vertebrate Genome Annotation Database', 'url'=> 'http://www.sanger.ac.uk/resources/databases/vega/'),
	'wikipedia'=>array('name'=>'Wikipedia'),
	'wormbase' => array('name'=>'WormBase'),
	'zfin'=>array('name'=>'Zebrafish'),
	);
	
	function __construct()
	{
		$a = array('terminologies','datasets','obo');
		foreach($a AS $b) {
			foreach($this->$b AS $ns => $obj) {
				$this->all_ns[$ns] = $obj;
				$this->all_ns[$ns]['bio2rdf_uri']  = self::base_uri.$ns.':';
				$this->all_ns[$ns.'_vocabulary']['priority_uri'] = self::base_uri.$ns.'_vocabulary:';
				$this->all_ns[$ns.'_resource']['priority_uri']   = self::base_uri.$ns.'_resource:';
				
				if(isset($obj['uri'])) {
					$this->all_ns[$ns]['priority_uri'] = $obj['uri'];
				} else {
					$this->all_ns[$ns]['priority_uri'] = self::base_uri.$ns.':';
				}
				
				// generate the namespace map
				if(isset($obj['synonyms'])) {
					if(!is_array($obj['synonyms'])) {
						$obj['synonyms'] = array($obj['synonyms']);
					}
					foreach($obj['synonyms'] AS $syn) {
						$syn = strtolower(str_replace(array(" ","-","_","."),"",$syn));
						$this->ns_map[$syn][] = $ns;
					}
				}
				
				// obo
				if($b == "obo") {
					$this->all_ns[$ns]['uri'] = "http://purl.obolibrary.org/$ns/";
					$this->ns_map["obo$ns"][] = $ns; 
					
					if(!isset($obj['identifiers.org'])) 
						$this->all_ns[$ns]['identifiers.org'] = "http://identifiers.org/obo.$ns/";
					else $this->all_ns[$ns]['identifiers.org'] = $obj['identifiers.org'];
				}
				
			} //foreach
		} //foreach
	}
   
	/** Determine whether a namespace is registered 
	 *
	 * @param string $ns The namespace to check the registry against
	 * @return TRUE if found, else FALSE
	*/
	function isNS($ns)
	{
		if(!isset($this->all_ns[$ns])) return FALSE;
		return TRUE;
	}
	/** Dynamically (temporarily) add an namespace to the registry 
	 *
	 * @param string $ns The namespace to add
	 * @return bool TRUE on execution
	 */
	function addNS($ns)
	{
		if(!isset($this->all_ns[$ns])) {
			$this->all_ns[$ns]['bio2rdf_uri'] = self::base_uri.$ns.':';
		}
		return TRUE;
	}
	
	/** Get the base URI for a namespace
	 *
	 * @param string $ns The namespace to get its base URI
	 * @return The base URI for query namespace, or FALSE if not found
	 */
	function getNSURI($ns)
	{
		if($this->isNS($ns) == FALSE) {
			trigger_error("Unable to find $ns in registry");
			return FALSE;
		}
		return $this->all_ns[$ns]['priority_uri'];
	}
	
	function getProviderURI($ns)
	{
		if($this->isNS($ns)) {
			if(isset($this->all_ns[$ns]['uri'])) return $this->all_ns[$ns]['uri'];
			else return null;
		}	
	}

	function getIDENTIFIERS_URI($ns)
	{
		if($this->isNS($ns)) {
			if(isset($this->all_ns[$ns]['identifiers.org'])) return $this->all_ns[$ns]['identifiers.org'];
			else return null;
		}
		return null;
	}
	
	function getBio2RDF_URI($ns)
	{
		if($this->isNS($ns)) {
			return $this->all_ns[$ns]['bio2rdf'];
		}
	}
	
	function getResourceURI($ns)
	{
		if($this->isNS($ns)) {
			return $this->all_ns[$ns."_resource"]['uri'];
		}
	}
	
	function getVocabularyURI($ns)
	{
		if($this->isNS($ns)) {
			return $this->all_ns[$ns."_vocabulary"]['uri'];
		}
	}
	
	
	/** Parse a prefixed name (e.g. GI:12345) into its constitutive parts
	 * 
	 * @param string $prefixed_name the prefixed name
	 * @param string $ns the namespace, if found
	 * @param string $identifier the identifier
	 * @param string $delimiter the delimiter to be used
	 * @return TRUE on execution
	 */
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
	
	function ParseQName($qname,&$ns,&$id,$delimiter=':')
	{
		return $this->ParsePrefixedName($qname,$ns,$id,$delimiter);
	}
	
	/** Parse a qualified name (e.g. GI:12345) into its constitutive parts
	 * 
	 * @param string $qname the qualified name
	 * @param string $ns the namespace, if found
	 * @param string $identifier the identifier
	 * @param string $delimiter the delimiter to be used
	 * @return TRUE on execution
	 */
	function MapQName($qname,$delimiter=':')
	{
		$ns = '';$id='';
		$this->ParsePrefixedName($qname,$ns,$id,$delimiter);
		if($this->isNS($ns) === FALSE) {		
			$ns = str_replace(array(" ","-","_","."),"",$ns);
			// try to map the namespace
			if(isset($this->ns_map[$ns])) {
				$ns = $this->ns_map[$ns][0];
			} else {
				// no match
				trigger_error("Invalid namespace $ns for $qname", E_USER_ERROR); 
				return FALSE;
			}
		}
		
		return "$ns:$id";
	}
	
	/** Get the fully qualified URI for a qualified/prefixed name
	 * 
	 * @param string $qname The qualified name
	 * @return string The Fully Qualified URI
	 */
	function getFQURI($qname) 
	{
		$this->ParsePrefixedName($qname,$ns,$id);		
		if(!$this->isNS($ns)) {
			trigger_error("Invalid qname ".$ns. " for $qname", E_USER_ERROR);
			exit;
		}
		return $this->getNSURI($ns).$id;
	}
	
	/** Get a turtle/n3 formatted Fully Qualified URI
	 *
	 * @param string $qname The qualified name
	 * @return string <uri>
	 */
	function getFQURI_TTL($qname)
	{
		return "<".$this->getFQURI($qname).">";
	}
	
	/** Get the turtle prefix for a namespace
	 *
	 * @param string $ns the namespace
	 * @return string The Turtle formatted prefix declaration
	 */	 
	function GetTTLPrefix($ns)
	{
		 return '@prefix '.$ns.': <'.$this->getNSURI($ns).'> .'.PHP_EOL;
	}
	
	/** Generate a list of prefixed based uris for inclusion as header of a turtle file 
	 * 
	 * return string A turtle-formated list of prefixes
	 */
	function GenerateTTLHeader()
	{
		$buf = '';
		foreach($this->all_ns AS $ns => $obj) {
			$buf .= $this->GetTTLPrefix($ns);
		}
		return $buf;
	}
}
