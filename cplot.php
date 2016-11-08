<?php
require_once 'phplot/phplot.php';

class CPlot {
	private static $instance=null;
	
	private function __construct($results) {
		$data = array();
		$end=false;
		$x=0;
		$y=0;
		$maxY=0;
        foreach($results as $k=>$doc) {
        	if($doc['sqm']>$maxY)$maxY=$doc['sqm'];
        	if($doc['bm25']>$maxY)$maxY=$doc['bm25'];
        }
        $maxY=(round($maxY)+1);
	    $lements=0;
		foreach($results as $k=>$doc)
		if($doc['sqm']>0 || $doc['bm25']>0)
        $elements++;
       	$delta=$maxY/$elements;
     		
		foreach($results as $k=>$doc) {
        	if($doc['sqm']>0 || $doc['bm25']>0)
	  		$data[] = array($x, $y, $doc['sqm'],$doc['bm25']);
	  		$x+=1;
	  		$y+=$delta;
	  	}
	  	//$elements=count($data);
	  	$plot = new PHPlot(800, 600);
		$plot->SetImageBorderType('plain');
		$plot->SetPlotType('lines');
		$plot->SetDataType('data-data');
		$plot->SetDataValues($data);
		$plot->SetTitle('Verbosity matching');
		$plot->SetLegend(array('Scarto quadratico medio', 'ITD'));
		$plot->SetPlotAreaWorld(0, 0, $elements, $maxY);
		$plot->SetXDataLabelPos('plotup');
		$plot->SetXTickIncrement(5);
		$plot->SetXLabelType('data');
		$plot->SetPrecisionX(0.5);
		$plot->SetYTickIncrement(2.5);
		$plot->SetYLabelType('data');
		$plot->SetPrecisionY(1);
		$plot->SetDrawXGrid(false);
		$plot->SetDrawYGrid(false);
		$plot->SetIsInline(true);
		$plot->SetOutputFile('diagramma.png');
		$plot->DrawGraph();
			
   }
	

   public static function go($results) {
   	    self::$instance = new CPlot($results);
   	    return self::$instance; 
   }
    	
	
}
