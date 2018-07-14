<!DOCTYPE html>
<?php
include '../../../../dbconfig.php';
include '../../../../Classes/Payment.php';
$q = $_GET['q'];
$conn = conn();

$sql = 'SELECT * FROM `deposit` dep,`company` comp WHERE dep.CompanyId=comp.CompanyId AND comp.CompanyName = "' . $q . '" AND DelFlg="N"';

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll();

$compinst = new \BarcomModel\Payment();

$BillFormNo = $compinst->GenerateTimestamp('RFN');
$BillRefNo = $compinst->GenerateTimestamp("");
$compid;

if (!empty($result)) {

    foreach ($result as $value) {
        $compid = $compinst->CompanyId = $value['CompanyId'];
        $compinst->CompanyName = $value['CompanyName'];
        $compinst->FaxNumber = $value['FaxNumber'];
        $compinst->ContactName = $value['ContactName'];
        $compinst->AgentName = $value['AgentName'];
        $compinst->PhoneNumber = $value['PhoneNumber'];
        $compinst->Email = $value['Email'];
        $compinst->AddressLine1 = $value['AddressLine1'];
        $compinst->AddressLine2 = $value['AddressLine2'];
        $compinst->AddressLine3 = $value['AddressLine3'];
        $compinst->Parish = $value['Parish'];

        $compinst->CompanyAddress = '<br>' . $compinst->AddressLine1 . '<br>' . $compinst->AddressLine2 . '<br>' . $compinst->AddressLine3 . '<br>' . $compinst->Parish;
        $compinst->CompanyBalance = $value['CurrentBalance'];
        $compinst->PreviousBalance = $value['PreviousBalance'];
        $compinst->CompanyName = $value['CompanyName'];
    }
} else {
    $sql = 'SELECT * FROM company WHERE CompanyName = "' . $q . '" ';
    $result = $conn->prepare($sql);
    $result->execute();
    foreach ($result as $value) {
        $compid = $compinst->CompanyId = $value['CompanyId'];
        $compinst->CompanyName = $value['CompanyName'];
        $compinst->FaxNumber = $value['FaxNumber'];
        $compinst->ContactName = $value['ContactName'];
        $compinst->AgentName = $value['AgentName'];
        $compinst->PhoneNumber = $value['PhoneNumber'];
        $compinst->Email = $value['Email'];

        $compinst->AddressLine1 = $value['AddressLine1'];
        $compinst->AddressLine2 = $value['AddressLine2'];
        $compinst->AddressLine3 = $value['AddressLine3'];
        $compinst->Parish = $value['Parish'];
        $compinst->CompanyName = $value['CompanyName'];

        $compinst->CompanyAddress = '<br>' . $compinst->AddressLine1 . '<br>' . $compinst->AddressLine2 . '<br>' . $compinst->AddressLine3 . '<br>' . $compinst->Parish;
    }
}
$conn = NULL;
$filteredPRN = $compinst->GetFilteredPRN($compid)
?> 

<div class="panel panel-info">
    <div class="panel-heading">
        <center> 
            <h3>
                <span class="label label-info"><?php echo $compinst->CompanyName; ?></span>                 
                <span class="label label-info"><?php echo $compinst->CompanyId; ?></span>
            </h3>
            <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#companydetails">Show Details</button>
        </center>
        <div id="companydetails" class="collapse">
            <div class="row center-block">
                <!-- <div class="col-xs-6">
                 <label>Company Id: </label><p><?php echo $compinst->CompanyId; ?></p> 
               </div>-->
                <input type="hidden" class="form-control" id="CompName" name="CompName" value="<?php echo $compinst->CompanyName; ?>" >
                <input type="hidden" class="form-control" id="CompId"  name="CompId" value="<?php echo $compinst->CompanyId; ?>">
                <input type="hidden" class="form-control" id="CompanyBalance"  name="CompanyBalance" value="<?php echo $compinst->CompanyBalance; ?>">
                <input type="hidden" class="form-control" id="BillRef"  name="BillRef" value="<?php echo $BillRefNo; ?>"> 
                <ul style="list-style: none">
                    <li><label>Agent:</label><?php echo $compinst->AgentName; ?></li> 
                    <li><span class="glyphicon glyphicon-phone"></span><label>Phone: </label><?php echo $compinst->PhoneNumber; ?></li> 
                    <li><span class="glyphicon glyphicon-print"></span><label>Fax: </label><?php echo $compinst->FaxNumber; ?></li>
                    <li><span class="glyphicon glyphicon-user"></span><label>Contact: </label><?php echo $compinst->ContactName; ?></li>
                    <li><label>Email: </label><a href="mailto:<?php echo $compinst->Email; ?>"> <?php echo $compinst->Email; ?> </a><br></li>
                    <li><span class="glyphicon glyphicon-send"></span> <label>Address: </label><?php echo $compinst->CompanyAddress; ?></li>                        
                </ul>
            </div>
        </div>
    </div>
    <div class="panel-body">  
        <div class="row">
            <div class="col-md-4">                 
                <label class="switch">
                    <input class="checkbox"  type="checkbox" id="IsPRN" name="IsPRN" >
                    <span class="slider round"></span>
                </label>                   
            </div>
            <div class="col-md-4">
                <br>
                <select class="form-control" name="PRNNo" id="PRNNo">
                    <?php echo $filteredPRN; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label id="lblBillFormNum" name='lblBillFormNum' style="font-size: large;font-stretch: semi-expanded;color:#ff0033"></label>
                <input type="hidden" class="form-control" name="BillFormNo" id="BillFormNo" value="<?php echo $BillFormNo; ?>" />   
            </div>
        </div>
        <div class="form-group" id="company" >
            <div class="row center-block">
                <div class="col-md-4">
                    <span class="glyphicon glyphicon-calendar"></span><label style="color: #00B0E8">Processs Date</label>
                    <input  class="form-control" name = "ProcessDate" id = "ProcessDate" required  autocomplete="off" readonly="readonly" value="<?php echo date('Y-m-d'); ?>"> 
                </div>
                <div class="col-md-4">
                    <span class="glyphicon glyphicon-calendar"></span><label>Start of Inspection</label>
                    <input type = "text" class="form-control" name = "InspectionDateStart" id = "InspectionDateStart" required placeholder="YYYY-MM-DD" autocomplete="off">
                </div>
                <div class="col-md-4">
                    <span class="glyphicon glyphicon-calendar"></span><label>End of Inspection</label>
                    <input type = "text" class="form-control" name = "InspectionDateEnd" id = "InspectionDateEnd" required placeholder="YYYY-MM-DD" autocomplete="off"> 
                </div> 
            </div>
        </div>
    </div>
</div>










