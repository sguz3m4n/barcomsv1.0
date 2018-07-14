<?php
include '../../../../dbconfig.php';
$conn = conn();
$sql = "SELECT * FROM prntransaction as A JOIN company as B ON A.CompanyId = B.CompanyId "
        . "JOIN prnmanage as C ON A.TranId = C.TranId WHERE C.PRNumber = '".$_REQUEST['id']."'";
//A.CloseDate IS NOT NULL AND 
if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    // Set parameters
    //$param_term = $_REQUEST['term'] . '%';
    //$stmt->bindParam(1, $param_term, PDO::PARAM_STR);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->fetch();

        // Check number of rows in the result set
        if (!empty($result)) {
            $closed = "";
            
            if(isset($result['CloseDate'])) {
                $closed = "checked disabled";
            }
            echo    
            '<div class="modal-dialog">
                  <!-- Modal content-->
                  <div class="modal-content">
                    <div class="modal-header" style="padding:35px 50px;">
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                      <h4 class="mdl"><span class="glyphicon glyphicon-lock"></span>Reassign PRN</h4>
                      <h5>'.$result['PRNumber'].'</h5>
                    </div>
                    <div class="modal-body" style="padding:40px 50px;">
                      <form role="form" action="/editprn" method="post">
                        <div class="form-group col-xs-12">
                          <label for="company"><span class="glyphicon glyphicon-user"></span>Company Name</label>
                          <input type="text" class="form-control" id="company" placeholder="" autocomplete="off" name="CompName" value="'.$result['CompanyName'].'">
                          <div id="results"></div>
                          <input type="hidden" class="form-control" id="TranId" name="TranId" placeholder="" value="'.$result['TranId'].'">
                          <input type="hidden" class="form-control" id="OldCompId" name="OldCompId" placeholder="" value="'.$result['CompanyId'].'">
                          <input type="hidden" class="form-control" id="RequestorId" name="RequestorId" value="'.$result['RequestorID'].'">
                          <input type="hidden" class="form-control" id="PRNumber" name="PRNumber" value="'.$result['PRNumber'].'">
                          <input type="hidden" class="form-control" id="RequestCount" name="RequestCount" value="'.$result['PRNCount'].'">
                          <input type="hidden" class="form-control" id="requestorname" name="EmpName" placeholder="" value="'.$result['RequestorName'].'">
                        </div>
                        <div id="extra-results"></div>
                        <div class="form-group col-xs-12">
                          <label for="officerid"><span class="glyphicon glyphicon-btc"></span>Requestor Name</label>
                          <input type="text" class="form-control" id="requestornamedisplay" name="EmpNameDisplay" placeholder="" disabled value="'.$result['RequestorName'].'">
                        </div>
                        <div class="form-group col-xs-12">
                          <label for="distance"><span class="glyphicon glyphicon-book"></span>Status</label>
                          <input type="text" class="form-control" id="distance" placeholder="" disabled value="'.$result['Status'].'">
                        </div>

                        <div class="form-group col-xs-6"> 
                          <button type="submit" class="btn btn-success btn-block" id="modalsubmit" name="submit"><span class="glyphicon glyphicon-off"></span>Reassign</button>
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