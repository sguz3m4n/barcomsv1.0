<!DOCTYPE html>
<?php
include '../../../../dbconfig.php';
include '../../../../Classes/Employee.php';
$q = $_GET['q'];
$conn = conn();

$sqlwthdep = "SELECT * FROM `paymentrates` payrate,`employee` emp WHERE emp.ratecode=payrate.ratecode AND CONCAT_WS(' ',emp.FirstName,emp.LastName) = '" . $q . "' AND DelFlg='N'";

$stmt = $conn->prepare($sqlwthdep);
$stmt->execute();
$result = $stmt->fetchAll();

$empinst = new BarcomModel\Employee();
$EmployeeAddress;
if (!empty($result)) {
    foreach ($result as $value) {
        $empinst->Natregno = $value['Natregno'];
        $empinst->FirstName = $value['FirstName'];
        $empinst->Initial = $value['Initial'];
        $empinst->LastName = $value['LastName'];
        $Name = $empinst->FirstName . " " . $empinst->LastName;
        $empinst->AddressLine1 = $value['AddressLine1'];
        $empinst->AddressLine2 = $value['AddressLine2'];
        $empinst->AddressLine3 = $value['AddressLine3'];
        $empinst->AddressLine4 = $value['AddressLine4'];
        $empinst->PayRate = $value['RateAmount'];
        ($empinst->PayRate > 0 ? $empinst->PayRate : $empinst->PayRate = 0);
        $empinst->Parish = $value['Parish'];
        $Address = $empinst->AddressLine1 . " " . $empinst->AddressLine2 . " " . $empinst->AddressLine3 . "," . $empinst->Parish;
        $empinst->CellNo = $value['CellNo'];
        $empinst->HomePhone = $value['HomePhone'];
        $empinst->Email = $value['Email'];
        $empinst->RoleName = $value['RoleName'];
        $empinst->Gender = $value['Gender'];
        if ($empinst->Gender == "M") {
            $Gender = 'Male';
        } else {
            $Gender = 'Female';
        }
        $birthDate = $empinst->DateOfBirth = $value['DateOfBirth'];
        $empinst->Age = $age = (date('Y') - date('Y', strtotime($birthDate)));
        $empinst->Age = $age;

        $EmployeeAddress = $empinst->AddressLine1 . '<br>' . $empinst->AddressLine2 . '<br>' . $empinst->AddressLine3 . '<br>' . $empinst->Parish;
    }
} else {
    $sqlnodep = "SELECT * FROM `employee` emp WHERE CONCAT_WS(' ',emp.FirstName,emp.LastName) = '" . $q . "'";

    $result = $conn->prepare($sqlnodep);
    $result->execute();
    if (!empty($result)) {
        foreach ($result as $value) {
            $empinst->Natregno = $value['Natregno'];
            $empinst->FirstName = $value['FirstName'];
            $empinst->Initial = $value['Initial'];
            $empinst->LastName = $value['LastName'];
            $Name = $empinst->FirstName . " " . $empinst->LastName;
            $empinst->AddressLine1 = $value['AddressLine1'];
            $empinst->AddressLine2 = $value['AddressLine2'];
            $empinst->AddressLine3 = $value['AddressLine3'];
            $empinst->AddressLine4 = $value['AddressLine4'];
            $empinst->PayRate = 0;
            $empinst->Parish = $value['Parish'];
            $Address = $empinst->AddressLine1 . " " . $empinst->AddressLine2 . " " . $empinst->AddressLine3 . "," . $empinst->Parish;
            $empinst->CellNo = $value['CellNo'];
            $empinst->HomePhone = $value['HomePhone'];
            $empinst->Email = $value['Email'];
            $empinst->RoleName = $value['RoleName'];
            $empinst->Gender = $value['Gender'];
            if ($empinst->Gender == "M") {
                $Gender = 'Male';
            } else {
                $Gender = 'Female';
            }
            $birthDate = $empinst->DateOfBirth = $value['DateOfBirth'];
            $empinst->Age = $age = (date('Y') - date('Y', strtotime($birthDate)));
            $empinst->Age = $age;

            $EmployeeAddress = $empinst->AddressLine1 . '<br>' . $empinst->AddressLine2 . '<br>' . $empinst->AddressLine3 . '<br>' . $empinst->Parish;
        }
    }
}

$conn = NULL;
?> 
<div class="panel panel-info">
    <div class="panel-heading">
        <center>
            <h3>
                <ul style="list-style: none">
                    <li>
                        <img src="../assets/global/img/customs logo no back.png" alt="" height="50"/>
                        <span class="label label-info"><?php echo $Name; ?></span>&nbsp;<span class="label label-info"> <?php echo $empinst->Natregno; ?></span> 
                        <br> Rate<span class="label label-info"><?php echo '$ ' . $empinst->PayRate; ?></span>
                        <input type="hidden" class="form-control" id="Natregno"  name="Natregno" value="<?php echo $empinst->Natregno; ?>">
                        <input type="hidden" class="form-control" id="PayRate"  name="PayRate" value="<?php echo $empinst->PayRate; ?>">
                        <input type="hidden" class="form-control" id="EmpName"  name="EmpName" value="<?php echo $Name; ?>">

                    </li> 
                </ul>
                <span class="label label-info"><?php echo $empinst->RoleName; ?></span> 
            </h3>   
            <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#employeedetails">Show Details</button>
        </center>
        <div id="employeedetails" class="collapse">
            <ul style="list-style: none">
                <li><label>Age: </label><?php echo $empinst->Age; ?>&nbsp;&nbsp;&nbsp;<label>Gender: </label><?php echo $Gender; ?></li>
                <li><label>Address: </label><?php echo $EmployeeAddress; ?></li>
                <li><span class="glyphicon glyphicon-phone"></span><label>Mobile: </label><?php echo $empinst->CellNo; ?></li> 
                <li><span class="glyphicon glyphicon-phone-alt"></span><label>Phone: </label><?php echo $empinst->HomePhone; ?></li>
                <li><a href="mailto:<?php echo $empinst->Email; ?>"> <?php echo $empinst->Email; ?> </a></li>
            </ul> 
        </div>

    </div>
</div>







