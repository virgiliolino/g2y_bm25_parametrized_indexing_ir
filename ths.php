<form name="formBM25" method="get" action="ths.php">
    Inserisci il termine da ricercare all'interno della collezione
    <input type="text" name="q" placeholder="Verba" />
    <input type="submit" value="volant"/>
</form>
<?php
ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);

if(isset($_GET['q'])) {
	require 'bm25plus.php';
	require 'cplot.php';
	$myBM25=new BM25Plus();
	$myBM25->gatherCollection('collection')
	      ->setQuery($_GET['q'])
          ->createIndex()
          ->bm25WeightPLUSitd()
          ->sqm();
    //exit(var_dump($myBM25->getScores()));
    //$tmpScores=$myBM25->getScores();
    //$values[]=$tmpScores['sqm'];
    //$values[]=$tmpScores['bm25'];
    CPlot::go($myBM25->getScores());
    $t=microtime();
?>
<img src="diagramma.png?t=<?php echo $t; ?>"/>
<?php }  ?>