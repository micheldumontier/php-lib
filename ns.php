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
  private $all_ns = null;
  private $all_uri = null;
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
	'aa' => array('name'=>'Amino Acid Ontology','uri'=>'http://www.co-ode.org/ontologies/amino-acid/2006/05/18/amino-acid.owl#','alt-uri'=>'http://www.co-ode.org/ontologies/amino-acid/2005/10/11/amino-acid.owl#','example-id'=>'F'),
	'aba' => array('name'=>'ABA Adult Mouse Brain','uri'=>'http://mouse.brain-map.org/atlas/index.html#','example-id'=>'ENTm2'),
	'adw' => array('name'=>'Animal natural history and life history','uri'=>'http://www.owl-ontologies.com/unnamed.owl#','example-id'=>'Female_sexual_maturity'),
	'birnlex'   => array('name'=>'BIRNLEX','uri'=>'http://bioontology.org/projects/ontologies/birnlex#','alt-uri'=>'http://ontology.neuinfo.org/NIF/Backend/BIRNLex_annotation_properties.owl#'),
	'bao' => array('name'=>'BioAssay Ontology','uri'=>'http://www.bioassayontology.org/bao#','example-id'=>'BAO_0000979'),
	'bdo' => array('name'=>'Bone Dysplasia Ontology','uri'=>'http://purl.org/skeletome/bonedysplasia#','example-id'=>'Spondyloepiphyseal_dysplasia_Omani_type'),
	'bo' => array('name'=>'BOOK','uri'=>'http://purl.org/net/nknouf/ns/bibtex#','example-id'=>'Phdthesis'),
	'biopax'    => array('name' => 'synthetic BioPAX vocabulary','url'=>'http://www.biopax.org'),
	'biopaxl2'  => array('name' => 'BioPAX level 2','uri'=>'http://www.biopax.org/release/biopax-level2.owl#','url'=>'http://www.biopax.org'),
	'biopaxl3'  => array('name' => 'BioPAX level 3','uri'=>'http://www.biopax.org/release/biopax-level3.owl#','url'=>'http://www.biopax.org'),
	'bt' => array('name'=>'BioTop','uri'=>'http://purl.org/biotop/biotop.owl#','example-id'=>'CategorizationSystem'),
	'canco' => array('name'=>'Cancer Chemoprevention Ontology','uri'=>'http://bioontology.org/ontologies/ResearchArea.owl#','example-id'=>'Outcomes_Research'),
	'carelex' => array('name'=>'CareLEX','uri'=>'http://www.CareLex.org/2012/carelex.owl#'),
	'chem2bio2owl' => array('name'=>'Systems Chemical Biology/Chemogenomics ','uri'=>'http://chem2bio2rdf.org/chem2bio2rdf.owl#','example-id'=>'Acylation'),
	'cogpo' => array('name'=>'Cognitive Paradigm Ontology','uri'=>'http://www.cogpo.org/ontologies/','alt-uri'=>array('http://www.cogpo.org/ontologies/CogPOver1.owl#','http://www.cogpo.org/ontologies/CogPOver2010.owl#'),'example-id'=>'COGPO_00021'),
	'cogat' => array('name'=>'Cognitive Atlas','uri'=>'http://www.cognitiveatlas.org/ontology/cogat.owl#','example-id'=>'CAO_01019'),
	'ctcae' => array('name'=>'Common Terminology Criteria for Adverse Events','uri'=>'http://ncicb.nci.nih.gov/xml/owl/EVS/ctcae.owl#','example-id'=>'Grade_4_Colonic_perforation'),
	'cno' => array('name'=>'Computational Neuroscience Ontology','uri'=>'http://purl.org/incf/ontology/Computational_Neurosciences/cno_alpha.owl#','example-id'=>'cno_0000001'),
	'cpo' => array('name'=>'Cell Phenotype Ontology','uri'=>'http://phenomebrowser.net/cellphenotype.owl#','example-id'=>'C3PO:000000015'),
	'cpr'=>array('name'=>'Computer-based Patient Record Ontology (CPR)','uri'=>'http://purl.org/cpr/','alt-uri'=>array('http://purl.org/cpr/0.85#','http://bioontology.org/wiki/index.php/DallasWorkshop#'),'example-id'=>'abnormal-homeostasis'),
	'dc'   => array('name'=>'Dublin Core Metatdata','uri' =>'http://purl.org/dc/terms/'),
	'diag' => array('name'=>'Diagnostic Ontology','synonyms'=>'diagnosticont','uri'=>'http://www.owl-ontologies.com/RCTOntology.owl#','example-id'=>'SecondaryHypothesis'),
	'ddi' => array('name'=>'Ontology for Drug Discovery Investigations','uri'=>'http://purl.org/ddi/owl#','example-id'=>'pharmacophore'),
	'dm' => array('name'=>'Ontology of Data Mining','synonyms'=>'ontodm','uri'=>'http://kt.ijs.si/panovp/OntoDM#','example-id'=>'OntoDM_733255'),
	'doap' => array('name'=>'Description of a Project (DOAP)','uri'=>'http://usefulinc.com/ns/doap#'),
	'eagle-i' => array('name'=>'eagle-i research resource ontology','uri'=>'','example-id'=>''),
	'ecg'=>array('name'=>'Electrocardiography Ontology','uri'=>'http://www.cvrgrid.org/files/ECGOntologyv1.owl#'),
	'epilepsy' => array('name'=>'Epilepsy','synonyms'=>'epileponto','alt-uri'=>'http://www.semanticweb.org/ontologies/2009/3/EpilepsyOntology.owl#','example-id'=>'Epileptic_Syndromes'),
	'ehdaa' => array('name'=>'Human developmental anatomy, abstract version, v2','uri'=>'http://purl.obolibrary.org/obo/EHDAA2#','example-id'=>'_0001816'),
	'foaf' => array('name'=>'Friend of a Friend (FOAF)','uri'=>'http://xmlns.com/foaf/0.1/'),
	'fda.devices'=>array('name'=>'FDA devices','uri'=>'http://purl.bioontology.org/ontology/FDA_Medical_Devices/'),
	'geonames'=>array('name'=>'Geonames','uri'=>'http://www.geonames.org/ontology#'),
	'geospecies'=>array('name'=>'Geospecies','uri'=>'http://rdf.geospecies.org/ont/gsontology#'),
	'gfo' => array('name'=>'General Formal Ontology (GFO)','uri'=>'http://www.onto-med.de/ontologies/gfo.owl#'),
	'gpd' => array('name'=>'Ontology of General Purpose Datatypes','uri'=>'http://kt.ijs.si/panovp/OntoDT#','example-id'=>'OntoDT_487147'),
	'hl7' => array('name'=>'HL7','uri'=>'http://purl.bioontology.org/ontology/HL7/'),
	'hom.ehs' => array('name'=>'HOM Elixhauser Scores','uri'=>'http://purl.bioontology.org/ontology/HOM_ElixhauserScores/','example-id'=>'Class_8'),
	'hom.harvard' => array('name'=>'HOM-HARVARD','uri'=>'http://purl.bioontology.org/ontology/HOM-UCARE_EPIC/','example-id'=>'MM_CLASS_118'),
	'hom.mdcdrg' => array('name'=>'HOM-MDCDRG','uri'=>'http://purl.bioontology.org/ontology/HOM-MDCDRG/','example-id'=>'MM_CLASS_451'),
	'hom.i9pcs' => array('name'=>'HOM-ICD9PCS','uri'=>'http://purl.bioontology.org/ontology/HOM-I9PCS/','example-id'=>'MM_CLASS_1637'),
	
	'icd9' => array('name' => 'International Classification of Disease v9'),
	'icd10'=> array('name' => 'International Classification of Disease v10'),
	'icnp' => array('name'=>'International Classification for Nursing Practice','uri'=>'http://www.icn.ch/icnp#','example-id'=>'Menstruation'),
	'invertebrata' => array('name'=>'Hewan Invertebrata','uri'=>'http://www.semanticweb.org/ontologies/HewanInvertebrata.owl#','example-id'=>'ordo_Solpugida'),
	'icps' => array('name'=>'ICPS Network',
		'alt-uri'=>array(
		'http://www.ICPS/ontologies/Activity.owl#',
		'http://www.ICPS/ontologies/ActionsToReduceRisk#',
		'http://www.ICPS/ontologies/AmelioratingActions.owl#',
		'http://www.ICPS/ontologies/BuildingStructure#',
		'http://www.ICPS/ontologies/Detection.owl#',
		'http://www.ICPS/ontologies/Furniture.owl#',
		'http://www.ICPS/ontologies/HospitalEquipment.owl#',
		'http://www.ICPS/ontologies/IncidentCharacteristic#',
		'http://www.ICPS/ontologies/MitigatingFactors.owl#',
		'http://www.ICPS/ontologies/OrganizationalOutcomes#',
		'http://www.ICPS/ontologies/Person#',
		'http://www.ICPS/ontologies/PatientSafetyIncident#',
		'http://www.ICPS/ontologies/PatientCharacteristics.owl#',
		
		),
		'example-id'=>''),
	'imgt' => array('name'=>'IMGT-ONTOLOGY','uri'=>'http://www.imgt.org/download/IMGT-ONTOLOGY/IMGT-ONTOLOGY-v1-0-1.owl#','example-id'=>'sterile_transcript'),
	'kisao' => array('name'=>'Kinetic Simulation Algorithm Ontology','uri'=>'http://www.biomodels.net/kisao/KISAO#','example-id'=>'KISAO_0000305'),
	'lhn' => array('name'=>'Loggerhead nesting','uri'=>'http://purl.obolibrary.org/obo/B8467#','example-id'=>'_0000127'),
	'natpro' => array('name'=>'Natural Products Ontology','uri'=>'http://www.owl-ontologies.com/NPOntology.owl#','example-id'=>''),
	'ncit' => array('name'=>'NCI Thesaurus','uri'=>'http://ncicb.nci.nih.gov/xml/owl/EVS/Thesaurus.owl#','example-id'=>'SMARCD1_wt_Allele'),
	'obo'  => array('name'=>'Open Biomedical Ontologies','uri'=>'http://purl.obolibrary.org/obo/','alt-uri'=>'http://purl.org/obo/owl/'),
	'oboe' => array('name'=>'OBOE',
		
		'alt-uri'=>array('http://ecoinformatics.org/oboe/oboe.1.0/oboe-core.owl#',
						'http://ecoinformatics.org/oboe/oboe.1.0/oboe-chemistry.owl#',
						 'http://ecoinformatics.org/oboe/oboe.1.0/oboe-biology.owl#',
						 'http://ecoinformatics.org/oboe/oboe.1.0/oboe-spatial.owl#',
						 'http://ecoinformatics.org/oboe/oboe.1.0/oboe-ecology.owl#',
						 'http://ecoinformatics.org/oboe/oboe.1.0/oboe-characteristics.owl#',
						 'http://ecoinformatics.org/oboe/oboe.1.0/oboe-anatomy.owl#',
						 'http://ecoinformatics.org/oboe/oboe.1.0/oboe-taxa.owl#',
						 'http://ecoinformatics.org/oboe/oboe.1.0/oboe-environment.owl#',
						 'http://ecoinformatics.org/oboe/oboe.1.0/oboe-temporal.owl#'),
						 'example-id'=>'Unit'),
	'oboe.sbc' => array('name'=>'OBOE SBC','synonyms'=>'obo_sbc','uri'=>'https://code.ecoinformatics.org/code/semtools/trunk/dev/oboe/oboe-sbc.owl#','example-id'=>'PrimaryProduction'),
	'ocre' => array('name'=>'Ontology of Clinical Research (OCRe)','uri'=>'http://purl.org/net/OCRe/OCRe.owl#',
		'alt-uri'=>array('http://purl.org/net/OCRe/statistics.owl#','http://purl.org/net/OCRe/study_design.owl#','http://purl.org/net/OCRe/study_protocol.owl#'),'example-id'=>'OCRE400040'),
	'omv'  => array('name'=>'Ontoloy Metadata Vocabulary (OMV)','uri'=>'http://omv.ontoware.org/2005/05/ontology#'),
	'ooevv' => array('name'=>'Ontology of Experimental Variables and Values','uri'=>'http://bmkeg.isi.edu/ooevv/','example-id'=>'edu.isi.bmkeg.ooevv.model.value.NumericValue'),
	'orphanet' => array('name'=>'Orphanet Ontology of Rare Diseases','uri'=>'http://www.orphanet.org/rdfns#','example-id'=>'pat_id_19912'),
	'peo' => array('name'=>'Parasite Experiment Ontology','uri'=>'http://paige.ctegd.uga.edu/ParasiteLifecycle.owl#','example-id'=>'Trypanosoma_brucei_metacyclic_trypomastigote'),	
	'pko' => array('name'=>'PKO_Re','uri'=>'http://www.semanticweb.org/ontologies/2009/10/25/PKO_Revamp.owl#','example-id'=>'KCNS3'),
	'phare'=> array('name'=>'Pharmacogenomics Ontology','uri'=>'http://www.stanford.edu/~coulet/phare.owl#'),
	'phenx' => array('name'=>'PhenX','uri'=>'file:///srv/ncbo/tssync/filerepo/3078/47819/','example-id'=>'130500'),
	'phylont' => array('name'=>'Phylogenetic Ontology','alt-uri'=>array('http://www.co-ode.org/ontologies/ont.owl#','http://www.semanticweb.org/ontologies/2011/7/Ontology1314368515010.owl#'),'example-id'=>'Nucleotide_Substitution_Model'),
	'pim' => array('name'=>'Personal Information Manager (PIM)','uri'=>'http://www.w3.org/2000/10/swap/pim/contact#'),
	'pma' => array('name'=>'PMA 2010','uri'=>'http://www.bioontology.org/pma.owl#','example-id'=>'PMA_728'),
	'prov' => array('name'=>'Provenance Ontology (PROV)','uri'=>'http://www.w3.org/ns/prov#'),
	'qibo' => array('name'=>'Quantitative Imaging Biomarker Ontology','alt-uri'=>'http://www.owl-ontologies.com/Ontology1298855822.owl#','example-id'=>'Electron_microscopy'),

	'lda' => array('name'=>'Ontology of Language Disorder in Autism','uri'=>'http://www.semanticweb.org/ontologies/2008/10/languageacquisition_autism.owl#','example-id'=>'Lexicalization'),
	'mesh' => array('name' => 'Medical Subject Headings (MeSH)'),
	'mhc' => array('name'=>'MaHCO - An MHC Ontology','uri'=>'http://purl.org/stemnet/MHC#','example-id'=>'MHC_Class_I_Allele'),
	'msi.nmr' => array('name'=>'NMR-instrument specific component of metabolomics investigations','uri'=>'http://msi-ontology.sourceforge.net/ontology/NMR.owl#','example-id'=>'MSI_400098'),
	'nif.agency'=>array('name'=>'NIF Granting Agency','uri'=>'http://ontology.neuinfo.org/NIF/DigitalEntities/NIF-Government-Granting-Agency.owl#'),
	'nif.backend'=>array('name'=>'NIF backend','uri'=>'http://ontology.neuinfo.org/NIF/Backend/','alt-uri'=>'http://ontology.neuinfo.org/NIF/Backend/BIRNLex-OBO-UBO.owl#'),
	'nif.cell' => array('name'=>'NIF Cell','uri'=>'http://ontology.neuinfo.org/NIF/BiomaterialEntities/NIF-Cell.owl#','example-id'=>'birnlex_2'),
	'nif_dysfunction' => array('name'=>'NIF Dysfunction','uri'=>'http://ontology.neuinfo.org/NIF/Dysfunction/NIF-Dysfunction.owl#','example-id'=>'birnlex_2098'),
	'nif.grossanatomy'=>array('name'=>'NIF Gross Anatomy ontology','uri'=>'http://ontology.neuinfo.org/NIF/BiomaterialEntities/NIF-GrossAnatomy.owl#'),
	'nif.investigation'=>array('name'=>'NIF investigation','uri'=>'http://ontology.neuinfo.org/NIF/DigitalEntities/NIF-Investigation.owl#'),
	'nif.molecule'=>array('name'=>'NIF Molecule','uri'=>'http://ontology.neuinfo.org/NIF/BiomaterialEntities/NIF-Molecule.owl#'),
	'nif.subcellular'=>array('name'=>'NIF subcellular ontology','uri'=>'http://ontology.neuinfo.org/NIF/BiomaterialEntities/NIF-Subcellular.owl#'),
	
	'nih.healthindicators' => array('name'=>'NIH health indicators','uri'=>'http://purl.bioontology.org/ontology/NIH_Health_Indicators/'),
	'owl-s' => array('name'=>'OWL-S','alt-uri'=>
		array('http://www.daml.org/services/owl-s/1.1/Service.owl#',
		'http://www.daml.org/services/owl-s/1.1/generic/Expression.owl#',
		'http://www.daml.org/services/owl-s/1.1/Process.owl#',
		'http://www.daml.org/services/owl-s/1.1/generic/ObjectList.owl#'
		)),
		
	'propreo' => array('name'=>'Proteomics data and process provenance','uri'=>'http://lsdis.cs.uga.edu/projects/glycomics/propreo#','example-id'=>'protein'),

	'radlex' => array('name'=>'RadLex','synonyms'=>'rid','uri'=>'http://bioontology.org/projects/ontologies/radlex/radlexOwlDlComponent#','example-id'=>'RID4329'),
	'rpo'=>array('name'=>'Rapid Phenotype Ontology','uri'=>'http://purl.bioontology.org/ontology/RPO','alt-uri'=>'http://www.semanticweb.org/ontologies/2012/5/Ontology1338526551855.owl#'),
	
	'rct' => array('name'=>'Randomized Controlled Trials (RCT) Ontology','uri'=>'http://www.owl-ontologies.com/RCTOntology.owl#','example-id'=>'KaplanMeierTimePoint'),
	'rtpo' => array('name'=>'Reproductive trait and phenotype ontology','uri'=>'http://purl.bioontology.org/ontology/REPO.owl#','example-id'=>'AbnormalParturition'),
	'sao' => array('name'=>'Subcellular Anatomy Ontology (SAO)','uri'=>'http://ccdb.ucsd.edu/SAO/1.2#','example-id'=>'sao1289741256'),
	'sdo' => array('name'=>'Sleep Domain Ontology','uri'=>'http://mimi.case.edu/ontologies/2009/1/SDO.owl#','alt-uri'=>'http://mimi.case.edu/ontologies/2009/9/DrugOntology#','example-id'=>'CentralSleepApneaDisorder'),
	'skeletome'=>array('name'=>'','uri'=>'http://purl.org/skeletome/phenotype#'),
	'sio'  => array('name'=>'Semanticscience Integrated Ontology (SIO)','uri'=>'http://semanticscience.org/resource/'),
	'sioc' => array('name'=>'Semantically Interlinked Online Communities (SIOC)','uri'=>'http://rdfs.org/sioc/ns#'),
	'skos' => array('name'=>'Simple Knowledge Organization System (SKOS)','uri'=>'http://www.w3.org/2004/02/skos/core#'),
	'sopharm' => array('name'=>'Suggested Ontology for Pharmacogenomics','alt-uri'=>'http://www.loria.fr/~coulet/ontology/unit/version1.9/unit.owl#','example-id'=>''),
	
	'snomed'=> array('name' => 'Systematized Nomenclature of Medicine'),
	'snpo' => array('name'=>'SNP-Ontology','uri'=>'','example-id'=>''),
	'spo'=>array('name'=>'Skin physiology ontology','uri'=>'http://purl.bioontology.org/ontology/SPO','alt-uri'=>array('http://www.semanticweb.org/ontologies/2008/8/SPO_lightweight_merged.owl#','http://www.semanticweb.org/ontologies/2008/8/MultiscaleSkinPhysiologyOntology.owl#')),
	'synapse' => array('name'=>'Synapse Ontology','synonyms'=>'syn','uri'=>'http://ncicb.nci.nih.gov/xml/owl/EVS/Thesaurus.owl#','example-id'=>'Primordial_Follicle'),
	'teo' => array('name'=>'Time Event Ontology','uri'=>'http://informatics.mayo.edu/TEO.owl#','example-id'=>'TEO_0000011'),
	'tm-mer' => array('name'=>'Traditional Medicine Meridian Value Sets','uri'=>'http://who.int/ictm/meridians#','example-id'=>'TM3316692'),
	'tma' => array('name'=>'Tissue Microarray Ontology','uri'=>'http://bioontology.org/ontologies/tma-minimal#','example-id'=>'slide'),
	'tmo'=>array('name'=>'Translational Medicine Ontology','uri'=>'http://www.w3.org/2001/sw/hcls/ns/transmed/'),

	'tok' => array('name'=>'TOK_Ontology','uri'=>'http://cui.unige.ch/isi/onto/tok/TOK.owl#','example-id'=>'Related_Term'),

	'who.bodysystem'=>array('name'=>'World Health Organization - Body Systems','uri'=>'http://who.int/bodysystem.owl#'),
	'who.iceci' => array('name'=>'International Classification of  External Causes of Injuries','uri'=>'http://who.int/iceci#','example-id'=>'iceciClass'),
	'who.icf' => array('name'=>'International Classification of Functioning, Disability and Health (ICF)','uri'=>'http://who.int/icf#','example-id'=>'s5808'),
	'who.signs'=>array('name'=>'World Health Organization - Signs and Symptoms','uri'=>'http://who.int/ictm/signsAndSymptoms#'),
	'who.factors'=>array('name','uri'=>'http://who.int/ictm/otherFactors#'),
	'who.constitution'=>array('name','uri'=>'http://who.int/ictm/constitution#'),
	'umls' => array('name' => 'UMLS'),
	'void' => array('name'=>'Vocabulary of Interlinked Datasets (VOID)','uri'=>'http://rdfs.org/ns/void#'),
	
	'bioonto.mesh' => array('name'=>'MeSH in OWL','uri'=>'http://bioonto.de/mesh.owl#'),
	'efo' => array('name'=>'Experimental Factors Ontology','uri'=>'http://www.ebi.ac.uk/efo/'),
	'nemo'=>array('name'=>'NEMO','uri'=>'http://purl.bioontology.org/NEMO/ontology/NEMO.owl#'),
	'neomark' => array('name'=>'Neomark Oral Cancer-Centred Ontology','uri'=>'http://www.neomark.eu/ontologies/neomark.owl#',
		'alt-uri'=>array('http://neomark.owl#','http://www.neomark.eu/ontologies/'),'example-id'=>'MacromollecularComplex'),
	'opb'=>array('name'=>'Ontology of Physics of Biology','uri'=>'http://bhi.washington.edu/OPB#'),
	'wgs84'=>array('name'=>'WGS84 Geo Positioning','uri'=>'http://www.w3.org/2003/01/geo/wgs84_pos#'),
	);
	
	// obo
	private $obo = array(
	'aeo' => array('name'=>'Anatomical Entity ontology','alt-uri'=>'http://purl.obolibrary.org/obo/AEO#'),
	'aero'=>array('name','alt-uri'=>'http://purl.obolibrary.org/obo/AERO/'),
	'ahfs' => array('name' => 'Pharmacologic-Therapeutic Classification System', 'url'=> 'http://www.ahfsdruginformation.com/class/index.aspx', 'type' => 'classification'),
	'apo' => array('name' => 'Ascomycetes Phenotype Ontology', 'url'=>'http://purl.bioontology.org/ontology/APO','type'=>'classification'),
	'atc' => array('name' => 'Anatomical Therapeutic Chemical Classification', 'url'=>'http://www.whocc.no/atc_ddd_index/','type' => 'classification'),
	'atm' => array('name'=>'African Traditional Medicine Ontology'),
	'biositemap' => array('name'=>'BioSiteMap', 'uri'=>'http://bioontology.org/ontologies/biositemap.owl#'),
	'bro'=>array('name'=>'Biomedical Resource Ontology','uri'=>'http://bioontology.org/ontologies/BiomedicalResourceOntology.owl#'),
	'bro.activity' => array('name'=>'Biomedical Resource Ontology - Activity','uri'=>'http://bioontology.org/ontologies/Activity.owl#','example-id'=>'Regulatory_Compliance'),
	'bto' => array('name' => 'BRENDA tissue ontology','synonyms'=>'brendatissueontology'),
	'caro' => array('name'=>'Common Anatomy Reference Ontology'),
	'ccu' => array('name'=>'Cell Culture Ontology','alt-uri'=>'http://purl.org/obo/owl/IEV#','example-id'=>'IEV_0000346'),
	'cco' => array('name' => 'Cell cycle ontology','synonyms'=>'cell cycle ontology','uri'=>'http://purl.obolibrary.org/obo/CCO#','example-id'=>'_G0018735'),
	'chebi' => array('name' => 'Chemical Entities of Biological Interest','part-of'=>'ebi','url'=>'http://www.ebi.ac.uk/chebi/',
		'alt-uri'=> array('http://www.ebi.ac.uk/chebi/searchId.do;?chebiId=','http://purl.obolibrary.org/obo/CHEBI#','http://purl.obolibrary.org/obo/chebi.owl#','http://www.loria.fr/~coulet/ontology/sopharm/version2.1/chebi.owl#')),
	'cheminf'=>array('name'=>'Chemical Information Ontology','uri'=>'http://semanticscience.org/resource/','alt-uri'=>'http://semanticscience.org/ontology/cheminf.owl/'),
	'clo' => array('name' => 'Cell line ontology','synonyms'=>'cl','alt-uri'=>'http://purl.org/obo/owl/CL#'),
	'cto' => array('name' => 'Cell type ontology','synonyms'=>'cell type ontology'),
	'do' => array('name' => 'Human Disease Ontology','synonyms'=>array('human disease ontology','doid'),
		'alt-uri'=>array('http://purl.org/obo/owl/DOID#','http://www.loria.fr/~coulet/ontology/sopharm/version2.1/disease_ontology.owl#')),
	'eco'       => array('name' => 'Evidence Code Ontology','synonyms'=>'evidence codes ontology'),
	'fbdv' => array('name'=>'Drosophila Development Ontology'),
	'fbdv_root' => array('name'=>'Drosophila Development Root Ontology'),
	'fma' => array('name'=>'Foundational Model of Anatomy','alt-uri'=>array('http://purl.org/obo/owlapi/fma#','http://sig.uw.edu/fma#','example-id'=>'Qualitative_coordinate_value')),
	'go'        => array('name' => 'Gene Ontology','synonyms'=>array('gene_ontology','gene ontology')),
	'granum' => array('name'=>'','uri'=>'http://chem.deri.ie/granatum/'),
	'gro' => array('name' =>'Gene Regulation Ontology','uri'=>'http://www.bootstrep.eu/ontology/GRO#'),
	'hp'        => array('name' => 'Human Phenotype Ontology (HPO)'),
	'hpio'      => array('name' => 'Human Pathogen Interactions ontology','uri'=>'http://www.semanticweb.org/ontologies/2010/5/22/Ontology1277229984000.owl#'),
	'iao' => array('name'=>'Information Artifact Ontology','alt-uri'=>'http://purl.obofoundry.org/obo/','example-id'=>'IAO_0000057'),
	'jerm' => array('name'=>'Sysmo JERM','uri'=>'http://www.mygrid.org.uk/ontology/JERMOntology#'),
	'lsm' => array('name'=>'Leukocyte surface markers ontology'),
	'obi' => array('name'=>'Ontology for biomedical investigation'),
	'mirna'=>array('name'=>'Ontology for MicroRNA target prediction','uri'=>'http://www.semanticweb.org/ontologies/2010/2/MiRNA-Ontology.owl#'),
	'miro' => array('name'=>'Mosquito insecticide resistance','uri'=>'','example-id'=>''),
	'plc' => array('name'=>'Ontology for Parasite LifeCycle','uri'=>'','example-id'=>''),
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
	'mp' => array('name'=>'mouse phenotype ontology'),
	'npo'=>array('name'=>'Nano particle ontology','uri'=>'http://purl.bioontology.org/ontology/npo#'),
	'oae' => array('name'=>'Ontology of Adverse Events (OAE)','uri'=>'http://purl.obolibrary.org/obo/oae.owl#','example-id'=>'complete_AE_recovery'),
	'ogi'=>array('name'=>'Ontology for Genetic Interval','uri'=>'http://purl.bioontology.org/ontology/OGI','alt-uri'=>'http://www.owl-ontologies.com/Ontology1207125891.owl#'),
	'pr' => array('name'=>'Protein Ontology'),
	'sbo' => array('name'=>'Systems Biology Ontology','synonyms'=>'Systems_Biology_Ontology'),
	'bfo' => array('name'=>'Basic Formal Ontology','uri'=>'http://www.ifomis.org/bfo/1.1#'),
	'span' => array('name'=>'Basic Formal Ontology SPAN','uri'=>'http://www.ifomis.org/bfo/1.1/span#','alt-uri'=>array('http://www.ifomis.org/bfo/1.0/span#','http://www.ifomis.org/span#')),
	'snap' => array('name'=>'Basic Formal Ontology SNAP','uri'=>'http://www.ifomis.org/bfo/1.1/snap#','alt-uri'=>'http://www.ifomis.org/bfo/1.0/snap#'),
	'so'=> array('name' => 'Sequence Ontology'),	
	'tads' => array('name'=>'tick gross anatomy ontology'),
	'taxon'=> array(
		'name' => 'NCBI Taxonomy',
		'synonyms'=>array('taxid','ncbitaxon','ncbitaxonomy','ncbi_taxonomy','taxonomy','NCBITaxon'),
		'identifiers.org'=>'taxon',
		'alt-uri'=>'http://purl.org/obo/owl/NCBITaxon#'),
	'teddy'=>array('name'=>'TEDDY','uri'=>'http://biomodels.net/teddy/TEDDY#'),
	'uo' => array('name'=>'Units Ontology','synonyms'=>'unitsontology','uri'=>'http://mimi.case.edu/ontologies/2009/1/UnitsOntology#','example-id'=>'US_pound'),
	'uberon'=>array('name'=>'UBERON','alt-uri'=>array('http://purl.org/obo/owl/UBERON#','http://purl.obolibrary.org/obo/UBERON#')),
	'vivo'=>array('name'=>'VIVO','uri'=>'http://vivoweb.org/ontology/core#'),
	'wheat' => array('name'=>'Wheat trait','synonyms'=>'co_wheat','uri'=>'http://purl.obolibrary.org/obo/CO_321#','example-id'=>'_0000029'),
	'wsio' => array('name'=>'Web-Service Interaction Ontology','alt-uri'=>array(
		'http://purl.obolibrary.org/obo/WSIO_operation#',
		'http://purl.obolibrary.org/obo/WSIO_compression#',
		'http://purl.obolibrary.org/obo/WSIO_data#'),
		'example-id'=>'_data'),
  );

  private $datasets = array(
  //'id' => array('name' => '','description' => '','uri' => '', 'url'=>'', 'synonyms' => array(), 'type' => 'dataset','terminology','part-of' => 'ns'),
	'3dmet'	=> array('name'=>'3Dmet'),
	'afcs'      => array('name' => 'UCSD Signaling Gateway','url'=>'http://www.signaling-gateway.org/'),
	'alfred'	=> array('name' => 'Allele Frequency Database','url'=>'http://alfred.med.yale.edu/'),
	'aracyc'    => array('name' => 'Aradopsis CYC genome database','url'=>'http://www.arabidopsis.org/biocyc/'),
	'beilstein' => array('name'=>'Beilstein Registry Number for organic compounds'),
	'biocyc'    => array('name' => 'CYC genome database'),
	'bioportal' => array('name' => 'BioPortal'),
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
	'genbank'	=> array('name' => 'GenBank','synonyms'=>array('genbank_nucl_gi','genbank_protein_gi','gb','GenPept')),
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
	'huge'		=> array(
		'name' => 'Database of Human Unidentified Gene-Encoded Large Proteins Analyzed',
		'url'=>'http://www.kazusa.or.jp/huge/'),
	'humancyc'  => array('name' => 'Human CYC database'),
	'inchi' => array('name'=>'InChI chemical identifier'),
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
	'knapsack' => array('name' => 'KNApSAcK: A Comprehensive Species-Metabolite Relationship Database','url'=>'http://kanaya.naist.jp/KNApSAcK/'),
	'lipidmaps' => array('name'=>'LIPIDMAPS database of lipds'),
	'maizegdb' => array('name'=>''),
	'matrixdb'  => array('name' => ''),
	'metacyc' => array('name' => 'Encyclopedia of Metabolic Pathways'),
	'mgi' => array('name'=>'Mouse Genome Informatics'),
	'mint' => array('name' => 'Molecular INTeraction database'),
	'mips' => array('name' => '','synonyms'=>'mppi'),
	'miriam' => array('name' => 'MIRIAM namespace registry'),
	'mirbase' => array('name'=> ''),
	'modbase' => array('name' => 'ModBase: Database of Comparative Protein Structure Models','url'=>'http://modbase.compbio.ucsf.edu/'),
	'mpact' => array('name' => ''),
	'mpi' => array('name' => '','synonyms'=>array('mpilit','mpiimex')),
	'mutdb' => array('name'=> 'MutDB contains annotations on human variation','url'=>'http://mutdb.org/'),
	'narcis' => array(
		'name' => 'NARCIS gateway to scholarly information in The Netherlands',
		'url'=>'http://www.narcis.nl/',
		'identifiers.org'=>'narcis'),
	'ncbi' => array('name' => 'National Center for Biotechnology Information'),
	'ncbo' => array('name' => 'National Center for Biomedical Ontology','url'=>'http://www.bioontology.org/'),
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
	'pdb'=> array('name' => 'Protein Databank','synonyms'=>array('wwpdb','pdbe','rcsb pdb','proteindatabank')),
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
	'pubchemcompound'=> array('name' => '', 'synonyms' => array('PubChem Compound','pubchem')),
	'pubchemsubstance'=> array('name' => '', 'synonyms' => array('PubChem Substance')),
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
				
				// check alternative uri and make array
				if(isset($obj['alt-uri'])) { 
					if(is_array($obj['alt-uri'])) $this->all_ns[$ns]['alt-uri'] = $obj['alt-uri'];
					else $this->all_ns[$ns]['alt-uri'] = array($obj['alt-uri']);
				} else {
					$this->all_ns[$ns]['alt-uri'][] = '';
				}
		
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
					$this->all_ns[$ns]['synonyms'] = $obj['synonyms'];
					foreach($obj['synonyms'] AS $syn) {
						$syn = strtolower(str_replace(array(" ","-","_","."),"",$syn));
						$this->ns_map[$syn][] = $ns;
					}
				}
				
				// obo
				if($b == "obo") {
					$obo_uri_list = array(
						"http://purl.obofoundry.org/$ns/",
						"http://purl.obolibrary.org/$ns/",
						'http://purl.org/obo/owl/'.strtoupper($ns).'#',
						"http://identifiers.org/obo.$ns/");
					if(isset($this->all_ns[$ns]['uri'])) $obo_uri_list[] = $this->all_ns[$ns]['uri'];
					foreach($obo_uri_list AS $uri) {			
						$this->all_ns[$ns]['alt-uri'][] = $uri;
					}
					// generate more alternatives
					if(isset($this->all_ns[$ns]['synonyms'])) {
						foreach($this->all_ns[$ns]['synonyms'] AS $s) {
							$this->all_ns[$ns]['alt-uri'] = array_merge($this->all_ns[$ns]['alt-uri'],array('http://purl.org/obo/owl/$s#'));
						}
					}
					
					// set the preferred
					$this->all_ns[$ns]['uri'] = "http://purl.obolibrary.org/$ns/";
					$this->all_ns[$ns]['type'] = 'obo';
					$this->ns_map["obo$ns"][] = $ns; 
					
					if(!isset($obj['identifiers.org'])) {
						$this->all_ns[$ns]['identifiers.org'] = "http://identifiers.org/obo.$ns/";
					} else $this->all_ns[$ns]['identifiers.org'] = $obj['identifiers.org'];
				}
				
				// generate the URI index for the bio2rdf_uri, publisher uri and identifiers.org
				$this->all_uri[$this->all_ns[$ns]['bio2rdf_uri']] = $ns;
				$this->all_uri[$this->all_ns[$ns]['priority_uri']] = $ns;
				if(isset($this->all_ns[$ns]['uri'])) {
					$this->all_uri[$this->all_ns[$ns]['uri']] = $ns;
				}
				if(isset($this->all_ns[$ns]['identifers.org'])) {
					$this->all_uri[ $this->all_ns[$ns]['identifers.org'] ] = $ns;
				}
				if(isset($this->all_ns[$ns]['alt-uri'])) {
					if(!is_array($this->all_ns[$ns]['alt-uri'])) $this->all_ns[$ns]['alt-uri'] = array($this->all_ns[$ns]['alt-uri']);
					foreach($this->all_ns[$ns]['alt-uri'] AS $alt) {
						$this->all_uri[$alt] = $ns;
					}
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
	
	function getObjectFromURI($uri)
	{
		if(isset($this->all_uri[$uri])) {
			return $this->all_uri[$uri];
		}
		return null;
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
