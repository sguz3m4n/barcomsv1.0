<!DOCTYPE html>
<?php
include '../../../../dbconfig.php';
include '../../../../Classes/Payment.php';
include '../../../../Classes/Company.php';
$q = $_GET['q'];
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
        $payinst->CompanyBalance = $value['CurrentBalance'];
        $payinst->PreviousBalance = $value['PreviousBalance'];

        $compinst->AddressLine1 = $value['AddressLine1'];
        $compinst->AddressLine2 = $value['AddressLine2'];
        $compinst->AddressLine3 = $value['AddressLine3'];
        $compinst->Parish = $value['Parish'];

        $payinst->CompanyAddress = $compinst->AddressLine1 . '<br>' . $compinst->AddressLine2 . '<br>' . $compinst->AddressLine3 . '<br>' . $compinst->Parish;
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
    }

    $payinst->CompanyAddress = $compinst->AddressLine1 . '<br>' . $compinst->AddressLine2 . '<br>' . $compinst->AddressLine3 . '<br>' . $compinst->Parish;
}

$conn = NULL;
?> 

<div class="panel panel-info">
    <div class="panel-heading">
        <center> 
            <h3>                
                <label></label><span class="label label-info"><?php echo $payinst->CompanyName; ?></span>
            </h3>          
        </center>
        <div class="form-group" id="company" > 
            <input type="hidden" class="form-control" id="CompName" name="CompName" value="<?php echo $payinst->CompanyName; ?>" >
            <input type="hidden" class="form-control" id="CompId" name="CompId" value="<?php echo $payinst->CompanyId; ?>" >
            <input type="hidden" class="form-control" id="PreviousBalance"  name="PreviousBalance" value="<?php echo number_format($payinst->PreviousBalance, 2, '.', ''); ?>">
            <input type="hidden" class="form-control" id="CompanyBalance"  name="CompanyBalance" value="<?php echo number_format($payinst->CompanyBalance, 2, '.', ''); ?>">
        </div>

    </div>
</div>












