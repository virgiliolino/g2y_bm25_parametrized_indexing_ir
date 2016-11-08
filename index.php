<?php

$parole=array('Gianni',
	      'Gianna',
	      'Custodire',
	      'Customizzare',
	      'Shakespeare',
	      'Shakerare',
	      'Gettone',
	      'Ghetto',
	      'Giglio',
              'Miglio');

echo "<table><tr><td> Parola </td><td> iSoundex </td><td> Soundex </td><td> Levenshtein distance </td>";
$precS=false;$precI=false;
foreach($parole as $p) {
  $s=soundex($p);
  $i=iSoundex($p);
  echo "<tr><td><B>".$p."</td><td>".$i."</td><td>".$s."</td>";
  if($precS) {
    echo "<td>leveshtein(".$precI.",".$i.")=".levenshtein($precI,$i)."</td>";    
    echo "<td>leveshtein(".$precS.",".$s.")=".levenshtein($precS,$s)."</td>";
    $precS=false;$precI=false;
  } else {
    $precS=$s;$precI=$i;
  }
  echo "</tr>";
}
echo "</table>";
function iSoundex($keys) {

  $keys=str_ireplace("B","1",$keys);
  $keys=str_ireplace("P","1",$keys);
  $keys=str_ireplace("V","1",$keys);
  $keys=str_ireplace("CA","2",$keys);
  $keys=str_ireplace("CO","2",$keys);
  $keys=str_ireplace("CU","2",$keys);

  $keys=str_ireplace("CI","2",$keys);
  $keys=str_ireplace("CE","2",$keys);
  $keys=str_ireplace("GI","2",$keys);
  $keys=str_ireplace("GE","2",$keys);
  $keys=str_ireplace("GA","5",$keys);
  $keys=str_ireplace("GO","5",$keys);
  $keys=str_ireplace("GU","5",$keys);

  $keys=str_ireplace("C","3",$keys);

  $keys=str_ireplace("G","3",$keys);
  $keys=str_ireplace("D","3",$keys);

  $keys=str_ireplace("T","3",$keys);
  $keys=str_ireplace("F","4",$keys);
  $keys=str_ireplace("H","",$keys);
  $keys=str_ireplace("J","2",$keys);

  $keys=str_ireplace("K","3",$keys);
  $keys=str_ireplace("F","4",$keys);
  $keys=str_ireplace("H","",$keys);
  $keys=str_ireplace("J","2",$keys);
  $keys=str_ireplace("L","6",$keys);
  $keys=str_ireplace("M","7",$keys);

  $keys=str_ireplace("N","7",$keys);
  $keys=str_ireplace("R","8",$keys);
  $keys=str_ireplace("S","9",$keys);
  $keys=str_ireplace("Y","",$keys);
  $keys=str_ireplace("Z","39",$keys);
  $keys=str_ireplace("X","39",$keys);
  $keys=str_ireplace("A","",$keys);
  $keys=str_ireplace("E","",$keys);
  $keys=str_ireplace("I","",$keys);
  $keys=str_ireplace("O","",$keys);
  $keys=str_ireplace("U","",$keys);


  return $keys;
}
?>