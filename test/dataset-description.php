<?php
/**
 *
 * LICENSE
 *
 * Copyright (C) 2013 Michel Dumontier. All rights reserved.
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * 
 *
 * @package    dataset description
 * @copyright  Copyright (c) 2013 Michel Dumontier
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */
 
require(__DIR__.'/../phplib.php');
set_error_handler('error_handler');

$rdf = new RDFFactory();
$rdf->getRegistry()->setLocalRegistry('/data/download/registry/');

$prefix = "test";

$bversion = 3;
$date   = date("Y-m-d");
$dataset_uri = "http://bio2rdf.org/bio2rdf_dataset:$prefix";
$bio2rdf_dataset_uri = $dataset_uri."-bio2rdf-$bversion";
$datafile = $prefix.".nt.gz";
$datafile_url = "http://download.bio2rdf.org/release/$bversion/$prefix/$datafile";


// source datafile
$source_file = (new DataResource($rdf))
	->setURI("http://some.other.publisher.org/download/remote_file.tab.gz")
	->setTitle("$prefix tab-formatted data file")
	->setRetrievedDate($date)
	->setFormat("text/tab-separated-value")
	->setFormat("application/gzip")	
	->setPublisher("http://some.other.publisher.org")
	->setLicense("http://some.other.publisher.org/non-standard-license");

// source dataset
$source_dataset = (new Dataset($rdf))
	->setURI($dataset_uri)
	->setPrefix($prefix)
	->setTitle("$prefix dataset")
	->setDescription("$prefix dataset")	
	->setHomepage("http://some.other.publisher.org")
	->setResource($source_file->getURI());

// bio2rdf
$script = "http://github.com/bio2rdf/bio2rdf-scripts/$prefix/$prefix.php";
$bio2rdf_datafile = (new DataResource($rdf))
	->setURI($datafile_url)
	->setTitle("Bio2RDF v$bversion of $prefix in RDF")
	->setDescription("Bio2RDF v$bversion of $prefix generated on $date by $script")
	->setCreateDate($date)
	->setCreator($script)
	->setSource($source_file->getURI())
	->setVersion($bversion)
	->setFormat("application/n-triples")	
	->setFormat("application/gzip")	
	->setPublisher("http://bio2rdf.org")
	->setLicense("http://creativecommons.org/licenses/by/3.0/")
	->setRights("use-share-modify")
	->setRights("by-attribution")
	->setRights("restricted-by-source-license");

$bio2rdf_dataset = (new Dataset($rdf))
	->setURI($bio2rdf_dataset_uri)
	->setPrefix($prefix)
	->setTitle("Bio2RDF v$bversion of $prefix dataset")
	->setDescription("Bio2RDF $bversion of $prefix dataset")	
	->setPublisher("http://bio2rdf.org")
	->setHomepage("http://download.bio2rdf.org/release/$bversion/$prefix/$prefix.html")
	->setResource($bio2rdf_datafile->getURI())
	->setRDFFile($bio2rdf_datafile->getURI())
	->setSPARQLEndpoint("http://test.bio2rdf.org/sparql");
	
file_put_contents($prefix."-dataset-description.nt", 
	$bio2rdf_dataset->toRDF()
	.$bio2rdf_datafile->toRDF()
	.$source_file->toRDF()
	.$source_dataset->toRDF()
);



	
	