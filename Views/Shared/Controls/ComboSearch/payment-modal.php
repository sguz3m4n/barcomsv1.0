<?php

include '../../../../dbconfig.php';
require '../../../../Classes/Payment.php';
$conn = conn();
$sql = "SELECT * FROM payment as A JOIN company as B ON A.CompanyId = B.CompanyId "
        . "JOIN employee as C ON A.natregno = C.natregno WHERE A.TransId = '" . $_REQUEST['id'] . "'"
        . " AND A.CloseDate IS NULL AND A.Status='Active'";
$compinst = new \BarcomModel\Payment();
$compbalance;

if ($stmt = $conn->prepare($sql)) {
    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->fetch();

        // Check number of rows in the result set
        if (!empty($result)) {
            if (!empty($compinst->GetCompanyBalance($result['CompanyId']))) {
                $compbalance = $compinst->GetCompanyBalance($result['CompanyId']);
            }
            $passengers = "";
            $subsistence = "";
            $subamt = 0;
            $passnum = 0;
            $action = "/editpayment";
            $closed = "";
            if ($result['Passengers'] == 'Y') {
                $passengers = "checked";
                $passnum = $result['PassengerNum'];
            } else {
                $passnum = 0;
            }
            if ($result['Subsistence'] == 'Y') {
                $subsistence = "checked";
                $subamt = $result['SubsistenceAmount'];
            } else {
                $subamt = 0;
            }
            if (isset($result['CloseDate'])) {
                $closed = "checked disabled";
                $action = "";
            }
            echo
            '<div class="modal-dialog">
                  <!-- Modal content-->
                  <div class="modal-content">
                    <div class="modal-header" style="padding:35px 50px;">
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                      <h4 class="mdl"><span class="glyphicon glyphicon-lock"></span>Edit Payment</h4>
                      <h5>' . $result['BillRefNo'] . '</h5>
                    </div>
                    <div class="modal-body" style="padding:40px 50px;">
                      <form role="form" action=' . $action . ' method="post">
                        <input type="hidden" class="form-control" id="transid" placeholder="" name="transid" value="' . $result['TransId'] . '">
                        <input type="hidden" class="form-control" id="compbal" placeholder="" name="compbal" value="' . $compbalance . '">                      
                        <input type="hidden" class="form-control" id="refund" placeholder="" name="refund" value="' . $result['TotalPaymentAmount'] . '"> 
                        <div class="form-group col-xs-6">
                          <label for="company"><span class="glyphicon glyphicon-user"></span>Company Name</label>
                          <input type="text" class="form-control" id="compname" placeholder="" name="compname"  readonly="readonly" value="' . $result['CompanyName'] . '">
                          <input type="hidden" class="form-control" id="compid" placeholder="" name="compid"  value="' . $result['CompanyId'] . '">
                        </div>
                        <div class="form-group col-xs-6">
                          <label for="officerid"><span class="glyphicon glyphicon-btc"></span>Officer Id</label>
                          <input type="text" class="form-control" id="natregno"   name="natregno" placeholder="" readonly="readonly" value="' . $result['Natregno'] . '">
                        </div>
                        <div class="form-group col-xs-3">
                          <label for="distance"><span class="glyphicon glyphicon-book"></span>Distance</label>
                          <input type="number" min="0" class="form-control" id="distance" name="distance" placeholder="" value="' . $result['Distance'] . '">
                        </div>
                        <div class="form-group col-xs-3">
                          <label for="hours"><span class="glyphicon glyphicon-bullhorn"></span>Hours</label>
                          <input type="number" min="0" class="form-control" id="hours" name="hours" placeholder="Hours Worked" value="' . $result['HoursWorked'] . '">
                        </div>
                        <div class="form-group col-xs-3">
                            <div class="checkbox">
                                <label><input type="checkbox" name="subsistence" id="subsistence" value="" ' . $subsistence . '>Subsistence</label>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" name="passengers" id="passengers" value="" ' . $passengers . '>Passengers</label>
                            </div>
                        </div>
                        <div class="form-group col-xs-3">
                          <label for="subamt">Subsistence Amt.</label>
                          <input type="text" min="0" step="1" class="form-control" id="subamt" name="subamt" placeholder="Subsistence Amt." value="' . $result['SubsistenceAmount'] . '">
                          <label for="passnum">Num. Pass.</label>
                          <input type="text" min="0" step="1" class="form-control" id="passnum" name="passnum" placeholder="Pass. #" value="' . $result['PassengerNum'] . '">
                        </div>
                        <div class="form-group col-xs-6"> 
                          <button type="submit" class="btn btn-success btn-block" id="update" name="update"><span class="glyphicon glyphicon-off"></span> Submit</button>
                        </div>
                        <div class="form-group col-xs-6"> 
                          <button type="button" class="btn btn-danger btn-default pull-left col-xs-12" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancel</button>
                        </div>
                      </form>
                    </div>
                    <div class="modal-footer">

                    </div>
                  </div>

                </div>';
        } else {
            echo "ERROR: Not able to execute $sql. ";
        }
    }
}
// Close onnection
$conn = NULL;
?>