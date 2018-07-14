<?php
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }
include '../../../Classes/PreReqNum.php';
$q = $_GET['q'];
$prnlist = array();
$prninst = new BarcomModel\PreReqNum();
$prninst->GeneratePRN($q);
$prninst->PRN;
echo'<table class="table table-condensed table-hover">
    <thead><strong>PRN(s) to disburse</strong></thead>     
        ';
for ($i = 0; $i < $q; $i ++) {

    //$prnlist[$i] = $rootnum . $i;
    // echo '<tr class="info"><td>' . \BarcomModel\PreReqNum::$prnlist[$i] . '</td> 
    echo '<tr class="info"> <td><span class="glyphicon glyphicon-tags"></span>'. $_SESSION['prnlist'][$i].'</td>  
    </tr>';
}
echo'</table>';
?>


