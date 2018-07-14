<!DOCTYPE html>
<?php
include '../../../../dbconfig.php';
include '../../../../Classes/Employee.php';
include '../../../../Classes/Payment.php';

$q = $_GET['q'][0];
$StartDate = $_GET['InspectionDateStart'][0];
$conn = conn();

$sql = "SELECT * FROM employee emp , paymentrates rates WHERE rates.ratecode=emp.ratecode AND CONCAT_WS(' ',FirstName,LastName) = '" . $q . "' AND DelFlg='N'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll();

$empinst = new BarcomModel\Employee();
$PayRate;
if (!empty($result)) {
    foreach ($result as $value) {
        /* $buffer_data['Natregno'] = $_GET["Natregno"] = $empinst->Natregno = $value['Natregno'];
          //$empinst->FirstName = $row['FirstName'];
          $_GET["RateAmount"] = $empinst->PayRate = $value['RateAmount'];
          $empinst->RateCode = $value['RateCode']; */
        $empinst->PayRate = $value['RateAmount'];
        $empinst->Natregno = $varid = $value['Natregno'];
        $empinst->RoleName = $value['RoleName'];
        $Employeename = $value['FirstName'] . ' ' . $value['LastName'];
        //$EndDate = $_GET['InspectionDateEnd'];
        $test = new \BarcomModel\PayScale();
        //$test->IsRange($StartDate, $EndDate);
        //If date selected isnt a range
        // if (!$test->IsRange($StartDate, $EndDate)) {
        //$wknd = $test->IsWeekend($StartDate);
        //Calculate For Weekend Rates
        if ($test->IsWeekend($StartDate)) {
            $test->GetPayRate($varid, NULL, "WKND");
        } else
        if ($test->IsHoliday($StartDate)) {
            $test->GetPayRate($varid, $StartDate, "HLDY");
        } else
        if ((!$test->IsWeekend($StartDate)) && (!$test->IsHoliday($StartDate))) {
            $test->GetPayRate($varid, NULL, "RGLR");
            //$test->PayRate = $test->GetRegular($empid);
        }
        //}
    }
} else {
    $sql = "SELECT * FROM employee emp WHERE CONCAT_WS(' ',FirstName,LastName) = '" . $q . "'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    if (!empty($result)) {
        foreach ($result as $value) {
            $empinst->PayRate = 0;
            $empinst->Natregno = $varid = $value['Natregno'];
            $empinst->RoleName = $value['RoleName'];
            $Employeename = $value['FirstName'] . ' ' . $value['LastName'];
        }
    }
}
$conn = NULL;
?>
<div class="row center-block">
    <div class="col-md-5">
        <div class="dashboard-stat dashboard-stat-v2 green" >
            <div class="visual">
                <i class="fa fa-comments"></i>
            </div>
            <div class="details">
                <div class="number">
                    <span data-value="2" id="submitteremployee" style="font-size: xx-large"    data-counter="counterup" ></span>
                </div>
                <div class="desc" id="submitteremployeename"><span class="glyphicon glyphicon-barcode"></span></div>
                <div class="desc" id=""><?php echo $empinst->RoleName; ?></div>
            </div>
        </div>
        <div class="dashboard-stat dashboard-stat-v2 blue" >
            <div class="visual">
                <i class="fa fa-comments"></i>
            </div>
            <div class="details">
                <div class="number">
                    <span class="glyphicon glyphicon-usd"></span> <span data-value="2" id="submitterrates" style="font-size: xx-large" data-counter="counterup">0</span>
                </div>
                <div class="desc"> Officer Pay Rate</div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="panel panel-info" id="empinfo">

            <div class="panel-heading"><h4>Customs Officer</h4></div>
            <div class="panel-body">
                <div class="form-group" id="employee">
                    <label> Hours</label> 
                    <input type="number" id="hours" name="hours" class="form-control" autocomplete="off" placeholder="Enter overtime hours" required>
                    <label> Distance</label> 
                    <input id="distance" name="distance" class="form-control" autocomplete="off" placeholder="Enter distance in km">
                    <div class="row center-block">
                        <div class="col-md-6">
                            <!-- <label>Subsistence ?</label>
                           <input class="checkbox" type="checkbox" id="subsistence" name="subsistence" >  
                          Rounded switch -->
                            <label>Subsistence ?</label>
                            <label class="switch">
                                <input class="checkbox"  type="checkbox" id="subsistence" name="subsistence" >
                                <span class="slider round"></span>
                            </label> 
                        </div>
                        <div class="col-md-6">
                            <!--<label>Passenger ?</label>
                            <input class="checkbox" type="checkbox" id="passenger" name="passenger" onclick='showpassnum()' >  
                             Rounded switch -->
                            <label>Passenger ?</label>
                            <label class="switch" >
                                <input class="checkbox"  type="checkbox" id="passenger" name="passenger" >
                                <span class="slider round"></span>
                            </label> 
                        </div>         
                    </div>
                    <div><input id="subsamt" name="subsamt" class="form-control"  placeholder="Subsistence Amount" value="0"></div>
                    <div><input id="passnum" name="passnum" class="form-control"  placeholder="Enter # of passengers" value="0"></div>
                    <div><input id="paylist" name="paylist" class="form-control" type="hidden" ></div>

                    <input type="hidden" class="form-control" id="EmpName"  name="EmpName" value="<?php echo $Employeename; ?>">
                    <div class="row center-block">
                        <div class="col-xs-3">
                            <input type="hidden" class="form-control" id="Natregno"  name="Natregno" value="<?php echo $empinst->Natregno; ?>">
                        </div>
                        <div class="col-xs-3">
                            <input type="hidden" class="form-control" id="PayRate"  name="PayRate" value="<?php echo BarcomModel\PayScale::$PayRate; ?>">
                        </div>
                        <div class="col-xs-3">
                            <input type="hidden" class="form-control" id="RateCode"  name="RateCode" value="<?php echo $empinst->RateCode; ?>">
                        </div>
                        <div class="col-xs-3">

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>   
</div>











