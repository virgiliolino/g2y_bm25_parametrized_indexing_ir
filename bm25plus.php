<?php
require 'exDir.php';


class BM25plus {    
private $_documents=null;
private $_queryTerms=null;
private $_index=null;
private $_scores=null;
private $_tfWeight = 1;
private $_dlWeight = 0.5;

public function __construct() {
   return $this;	
}


private function _debug($msg) {
	if(isset($_GET['debug'])) {
		echo $msg;
	}
	return $this;
}

public function gatherCollection($collectionPath) {
	$collectDir=new exDir($collectionPath,false,'/var/www/tesi/');
	$collectDocs=$collectDir->listRecursive();
	$key=0;
	foreach($collectDocs as $doc) {
       $this->_documents[$key]=$doc;
       $key++;		
	}
	 /*  $this->_documents = array(
                "d1" => "this document is is is is the first document that is quite long",
                "d2" => "this is yet another document that is very slightly longer",
                "d3" => "this isn't a very interesting string",
                "d4" => "this isn't a very interesting document either",
                "d5" => "less verbose",
                "d6" => "it is what it is and its not anything else as it is not what it is",
                "d7" => "is is is is is is is is is is is is",
                "d8" => "lexicom lexicom lexicom lexicom lexicom lexicom lexicom lexicom lexicom lexicom lexicom lexicom"
		      );
    */
	return $this;
}

private function _tokenize($phrase) {
    //$phrase=Normalizer::normalize($prhase);
	preg_match_all('/\w+/', $phrase, $matches);
    $tokens = $matches[0];
	return $tokens;
	
}

public function setQuery($phrase) {
	$this->_queryTerms=$this->_tokenize($phrase);
	return $this;
}

public function gettfWeight() {
	return $this->_tfWeight;
}

public function getdlWeight() {
	return $this->_dlWeight;
}

public function getScores() {
	return $this->_scores;
}

public function settfWeight($newWeight) {
    $this->_tfWeight=$newWeigth;
    return $this;
}

public function setdlWeight($newWeight) {
	$this->_dlWeight=$newWeight;
	return $this;
}




public function createIndex() {
        $this->_index = array('terms' => array(), 'length' => 0, 'documents' => array());
        foreach($this->_documents as $docID => $doc) {
                preg_match_all('/\w+/', $doc, $matches);
                // store the document length
                $this->_index ['documents'][$docID] = count($matches[0]);

                foreach($matches[0] as $match) {
                        if(!isset($this->_index ['terms'][$match])) {
                                $this->_index ['terms'][$match] = array();
                        }
                        if(!isset($this->_index ['terms'][$match][$docID])) {
                                $this->_index ['terms'][$match][$docID] = 0;
                        }
                        $this->_index ['terms'][$match][$docID]++;
                        $this->_index ['length']++;
                }        
        }
        $this->_index ['averageLength'] = $this->_index ['length']/count($this->_index ['documents']);
        return $this;
  }

  function bm25WeightPLUSitd() {
        	$this->_scores = array();
        	$count = count($this->_index['documents']);
	        foreach($this->_index['terms'] as $term=>$v) {
  				 $termineBM25=false;
  				 if(in_array($term,$this->_queryTerms)) 
                 $termineBM25=true;
    	         $this->_debug("<HR>termine:$term<BR>");
                 $df = count($this->_index['terms'][$term]);
                 foreach($this->_index['terms'][$term] as $docID => $tf) {
			        $this->_debug("<BR>documento: $docID => tf:$tf docLength:$docLength idf:$idf num:$num denom:$denom score:$score");
			        $docLength = $this->_index['documents'][$docID];
			  		$idf = log($count/$df);
			  		$num = ($this->_tfWeight + 1) * $tf;
			  		$denom = $this->_tfWeight * ((1 - $this->_dlWeight) + $this->_dlWeight 
			  						   * ($docLength / $this->_index['averageLength']))
			    					   + $tf;
			  		$score = $idf * ($num/$denom);
			  		if(isset($this->_scores[$docID]['score'])) {
			    		$this->_scores[$docID]['score'] += $score;
              		} else {
			    		$this->_scores[$docID]['score'] = $score;
			  		}
              		if($termineBM25) {
                        if(isset($this->_scores[$docID]['bm25'])) {
		                  	$this->_scores[$docID]['bm25'] += $score;	
              			} else {
		              	  	$this->_scores[$docID]['bm25'] = $score;
              			}
              		}			  
			  		$this->_scores[$docID]['terms'][$term]['score']=$score;
			     }
        }
		return $this;
  }

	function sqm() {
		foreach($this->_scores as $k=>$doc) {
                  $docScore=$doc['score'];
                  $N = count($this->_scores[$k]['terms']);
                  $sommatXi=0;
                  $sommatXis=0;
                  foreach($doc['terms'] as $kTerm=>$docTerm) {
                    $ivITD=0;
                    if($docTerm['score'])
                  	$invITD = $docScore / $docTerm['score'];
                   	$this->_scores[$k]['terms'][$kTerm]['itd']= $invITD;
                    $sommatXi+=$invITD;
                    $sommatXis+=$invITD*$invITD;
	              }
                  if($sommatXi>0 && $sommatXis >0) {
                  	$this->_scores[$k]['sqm']=(1/$N)*(sqrt(($N*$sommatXis)-$sommatXi*$sommatXi));
                  } else {
                  	$this->_scores[$k]['sqm']=0;
                  }
 		}
 		return $this;
   }
}