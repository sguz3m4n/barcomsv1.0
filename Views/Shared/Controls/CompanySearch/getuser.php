<!DOCTYPE html>
<?php
include '../../../../dbconfig.php';
include '../../../../Classes/Payment.php';
include '../../../../Classes/Company.php';
$q = $_GET['q'];
$t = $_GET['t'];
$conn = conn();
$payinst = new BarcomModel\Payment();
$compinst = new BarcomModel\Company();
$sqlcomprslt = 'SELECT * FROM `company` comp, `deposit` dep WHERE dep.CompanyId=comp.CompanyId AND comp.CompanyName = "' . $q . '" AND DelFlg="N"';

$stmt1 = $conn->prepare($sqlcomprslt);
$stmt1->execute();
$result = $stmt1->fetchAll();

if (!empty($result)) {
    foreach ($result as $value) {
        $payinst->CompanyId = $value['CompanyId'];
        $payinst->CompanyName = $value['CompanyName'];
        $payinst->FaxNumber = $value['FaxNumber'];
        $payinst->ContactName = $value['ContactName'];
        $payinst->AgentName = $value['AgentName'];
        $payinst->PhoneNumber = $value['PhoneNumber'];
        $payinst->Email = $value['Email'];
        //compinst->CompanyAddress = AddressBuilder();
        $payinst->CompanyBalance = $value['CurrentBalance'];
        $payinst->PreviousBalance = $value['PreviousBalance'];

        $compinst->AddressLine1 = $value['AddressLine1'];
        $compinst->AddressLine2 = $value['AddressLine2'];
        $compinst->AddressLine3 = $value['AddressLine3'];
        $compinst->Parish = $value['Parish'];

        $payinst->CompanyAddress = '<br>' . $compinst->AddressLine1 . '<br>' . $compinst->AddressLine2 . '<br>' . $compinst->AddressLine3 . '<br>' . $compinst->Parish;
    }
} else {
    $sql = 'SELECT * FROM company WHERE CompanyName = "' . $q . '"';
    $result = $conn->prepare($sql);
    $result->execute();
    foreach ($result as $value) {
        $payinst->CompanyId = $value['CompanyId'];
        $payinst->CompanyName = $value['CompanyName'];
        $payinst->FaxNumber = $value['FaxNumber'];
        $payinst->ContactName = $value['ContactName'];
        $payinst->AgentName = $value['AgentName'];
        $payinst->PhoneNumber = $value['PhoneNumber'];
        $payinst->Email = $value['Email'];

        $compinst->AddressLine1 = $value['AddressLine1'];
        $compinst->AddressLine2 = $value['AddressLine2'];
        $compinst->AddressLine3 = $value['AddressLine3'];
        $compinst->Parish = $value['Parish'];

        $payinst->CompanyAddress = $compinst->AddressLine1 . '<br>' . $compinst->AddressLine2 . '<br>' . $compinst->AddressLine3 . '<br>' . $compinst->Parish;
    }
}

if ($t == 'DEP') {
    $sqldepsmry = 'SELECT * FROM (
    SELECT * FROM deposittransaction WHERE CompanyId = (SELECT CompanyId FROM company WHERE CompanyName = "' . $q . '" ) AND TranType="' . $t . '" ORDER BY TranDate DESC LIMIT 10
) sub
ORDER BY CompanyId ASC';
    $stmt2 = $conn->prepare($sqldepsmry);
    $stmt2->execute();
    $result_array = $stmt2->fetchAll();
} else
if ($t == 'REF') {
    $sqldepsmry = 'SELECT * FROM (
    SELECT * FROM refundtransaction WHERE CompanyId = (SELECT CompanyId FROM company WHERE CompanyName = "' . $q . '" ) AND TranType="' . $t . '" ORDER BY TranDate DESC LIMIT 10
) sub
ORDER BY CompanyId ASC';
    $stmt2 = $conn->prepare($sqldepsmry);
    $stmt2->execute();
    $result_array = $stmt2->fetchAll();
}


$Address;
$conn = NULL;
?> 
<div class="panel panel-info">
    <div class="panel-heading">
        <center> 
            <h3> 
                <label></label><span class="label label-info"><?php echo $payinst->CompanyName; ?></span>
                <label></label><span class="label label-info"><?php echo $payinst->CompanyId; ?></span>
            </h3>
            <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo">Show Details</button>
        </center>
        <div id="demo" class="collapse">

            <ul style="list-style: none">
                <li><i class="fa fa-user" style="font-size: 20px"></i><?php echo $payinst->AgentName; ?></li> 
                <li><i class="fa fa-mobile-phone" style="font-size: 20px"></i><?php echo $payinst->PhoneNumber; ?></li> 
                <li><i class="fa fa-fax" style="font-size: 20px"></i><?php echo $payinst->FaxNumber; ?></li>
                <li><i class="fa fa-user" style="font-size: 20px"></i><?php echo $payinst->ContactName; ?></li>
                <li><i class="fa fa-envelope" style="font-size: 20px"></i><a href="mailto:<?php echo $payinst->Email; ?>"> <?php echo $payinst->Email; ?> </a></li>
                <li><i class="fa fa-envelope" style="font-size: 20px"></i><?php echo $payinst->CompanyAddress; ?></li>                        
            </ul>
            <div class="form-group" id="company" > 
                <input type="hidden" class="form-control" id="CompName" name="CompName" value="<?php echo $payinst->CompanyName; ?>" >
                <input type="hidden" class="form-control" id="CompId" name="CompId" value="<?php echo $payinst->CompanyId; ?>" >
                <input type="hidden" class="form-control" id="PreviousBalance"  name="PreviousBalance" value="<?php echo number_format($payinst->PreviousBalance, 2, '.', ''); ?>">
                <input type="hidden" class="form-control" id="CompanyBalance"  name="CompanyBalance" value="<?php echo number_format($payinst->CompanyBalance, 2, '.', ''); ?>">
            </div>

        </div>
    </div>
</div>
<div id="mySidenav" class="sidenav">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <table>  
        <?php
        if ($t == 'DEP') {
            echo'<thead><a>' . $payinst->CompanyName . ' Deposit Summary</a> </thead>';
            echo '<tr>'
            . '<th><a>Amount</a></th>'
            . '<th><a>Asycuda</a></th>';
            if (!empty($result_array)) {
                foreach ($result_array as $value) {
                    $t = strtotime($value['TranDate']);
                    echo '<tr>' .
                    '<td><a>' . '$' . number_format($value['TranAmt'], 2, '.', ',') . '</a></td>'
                    . '<td><a>' . $value['Asycuda'] . '</a></td>' .
                    '</tr>';
                }
            }
        } else
        if ($t == 'REF') {
            echo'<thead><a>' . $payinst->CompanyName . ' Refund Summary</a> </thead>';
            echo '<tr>'
            . '<th><a>Amount</a></th>'
            . '<th><a>Date</a></th>'
            . '<th><a>Time</a></th></tr>';
            if (!empty($result_array)) {
                foreach ($result_array as $value) {
                    $t = strtotime($value['TranDate']);
                    echo '<tr>' .
                    '<td><a>' . '$' . number_format($value['TranAmt'], 2, '.', ',') . '</a></td>'
                    . '<td><a>' . date('d-M-y', $t) . '</a></td>'
                    . '<td><a>' . date('h:i', $t) . '</a></td>' .
                    '</tr>';
                }
            }
        }
        ?>
    </table>
</div>











