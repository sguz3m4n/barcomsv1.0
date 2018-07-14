<?php

include '../../../../dbconfig.php';
$conn = conn();
/* $sql = "SELECT * FROM payment as A JOIN company as B ON A.CompanyId = B.CompanyId "
  . "JOIN employee as C ON A.natregno = C.natregno WHERE A.BillRefNo = '" . $_REQUEST['id'] . "'"
  . " AND A.CloseDate IS NULL"; */
$sql = "SELECT comp.companyid,comp.companyname,pymt.BillRefNo,pymt.RN,pymt.InspectionDate,pymt.StartDate,pymt.EndDate,pymt.PaymentDate,dpst.* FROM `payment` pymt,`company` comp,`deposit` dpst WHERE comp.companyid=pymt.companyid AND dpst.companyid=comp.companyid AND pymt.Status='Active' AND `BillRefNo`='" . $_REQUEST['id'] . "' ";
if ($stmt = $conn->prepare($sql)) {
    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->fetch();

        // Check number of rows in the result set
        if (!empty($result)) {
            $action = "/amendpayment";
            $closed = "";

            echo
            '<div class="modal-dialog">
                  <!-- Modal content-->
                  <div class="modal-content">
                    <div class="modal-header" style="padding:35px 50px;">
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                      <h4 class="mdl"><span class="glyphicon glyphicon-lock"></span>Amend Payment</h4>
                      <h5>' . $result['BillRefNo'] . '</h5>
                    </div>
                    <div class="modal-body" style="padding:40px 50px;">
                      <form role="form" action=' . $action . ' method="post">
                       
                      
                        <div class="form-group col-xs-6">
                          <label for="company"> Company Name <span class="label label-info">' . $result['companyname'] . '</span></label>
                          <input type="hidden" class="form-control" id="compname" placeholder="" name="compname"  value="' . $result['companyname'] . '">
                          <input type="hidden" class="form-control" id="InspectionDate" placeholder="" name="InspectionDate"  value="' . $result['InspectionDate'] . '">
                          <input type="hidden" class="form-control" id="PaymentDate" placeholder="" name="PaymentDate"  value="' . $result['PaymentDate'] . '">
                          <input type="hidden" class="form-control" id="StartDate" placeholder="" name="StartDate"  value="' . $result['StartDate'] . '">
                          <input type="hidden" class="form-control" id="EndDate" placeholder="" name="EndDate"  value="' . $result['EndDate'] . '">
                          <input type="hidden" class="form-control" id="BillRefNo" placeholder="" name="BillRefNo"  value="' . $result['BillRefNo'] . '">
                          <input type="hidden" class="form-control" id="RN" placeholder="" name="RN"  value="' . $result['RN'] . '">
                        </div>
                        <div class="form-group col-xs-6">
                          <label for="compid"> Company Id <span class="label label-info">' . $result['companyid'] . '</span></label> 
                          <input type="hidden" class="form-control" id="compid" placeholder="" name="compid"  value="' . $result['companyid'] . '">
                          <label for="compbal"> Company Balance <span class="label label-info">' . $result['CurrentBalance'] . '</span></label> 
                          <input type="hidden" class="form-control" id="compbal" placeholder="" name="compbal"  value="' . $result['CurrentBalance'] . '">
                          <input type="hidden" class="form-control" id="officerid"  disabled name="natregno" placeholder="" value="">
                        </div>
                        <div class="searchboxempmodal"> 
                            <div class="col-xs-12">
                                <span class="glyphicon glyphicon-search"></span>
                                <span class="glyphicon glyphicon-user"></span>
                                <input type="text" class="form-control" autocomplete="off" placeholder="Search Employee name or id..." id="searchempmodal" >
                                <div class="resultemp-modal" id="txtHintemp-modal">
                                    <span class="glyphicon glyphicon-list"></span> <b style="color: #0033ff">Employee info will be listed here...</b>
                                </div>
                            </div> 
                        </div>
                        <div class="form-group col-xs-3">
                          <label for="distance">Distance</label>
                          <input type="text" min="0" class="form-control" id="distance" name="distance" placeholder="" value="0">
                        </div>
                        <div class="form-group col-xs-3">
                          <label for="hours">Hours</label>
                          <input type="number" min="0" class="form-control" id="hours" name="hours" placeholder="Hours Worked" required value="0">
                        </div>
                        <div class="form-group col-xs-3">
                            <div class="checkbox">
                                <label><input type="checkbox" name="subsistence" id="subsistence">Subsistence</label>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" name="passengers" id="passengers">Passengers</label>
                            </div>
                        </div>
                        <div class="form-group col-xs-3">
                          <label for="subamt">Subsistence Amt.</label>
                          <input type="number" min="0" step="1" class="form-control" id="subamt" name="subamt" placeholder="Subsistence Amt." value="0">
                          <label for="passnum">Num. Pass.</label>
                          <input type="number" min="0" step="1" class="form-control" id="passnum" name="passnum" placeholder="Pass. #" value="0">
                        </div>
                        <div class="form-group col-xs-6"> 
                          <button type="submit" class="btn btn-success btn-block" id="submit-amendment" name="submit-amendment"><span class="glyphicon glyphicon-off"></span> Submit</button>
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