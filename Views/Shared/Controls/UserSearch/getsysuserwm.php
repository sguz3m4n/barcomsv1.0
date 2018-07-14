<!DOCTYPE html>
<?php
include '../../../../dbconfig.php';
include '../../../../Classes/Admin.php';

$q = $_GET['q'];
$conn = conn();

//$sql = "SELECT * FROM employee WHERE CONCAT_WS(' ',FirstName,LastName) = '" . $q . "'";
$sql = "SELECT * FROM users WHERE username='" . $q . "'";

$result = $conn->prepare($sql);
$result->execute();
$usrinst = new BarcomModel\UserModule();
$Natreg;
$Username;
$Firstname;
$Roles;
$LastLogin;
$Password;

foreach ($result as $value) {
    $Natreg = $usrinst->Natregno = $value['national_id'];
    $Firstname = $usrinst->FirstName = $value['firstname'];
    $Username = $usrinst->UserName = $value['username'];
    $Roles = $usrinst->Roles = $value['roles'];
    $LastLogin = $usrinst->LastLogin = $value['lastlogon'];
}
$conn = NULL;
?> 
<div class="panel panel-info">
    <div class="panel-heading">
        <center>
            <h3>
                <ul style="list-style: none">
                    <li>
                        <span class="label label-info"> <?php echo $Natreg; ?></span> <br> <br>
                     Display Name  <span class="label label-info"><?php echo $Firstname; ?></span>

                    </li>
                    <br>
                    <li>
                        User Role  <span class="label label-info"><?php echo $Roles; ?></span>     
                        User Name  <span class="label label-info"><?php echo $Username; ?></span>      
                    </li>
                </ul>
            </h3> 
        </center>
    </div>
</div>
</br>
<center>
    <div class="row">
        <!-- Trigger the modal with a button -->
        <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal" name="btn-edit">Edit System User</button>
    </div>  
</center>
<!-- Modal --> 
<form method="post">
    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">User Details</h4>
                </div>
                <div class="modal-body">
                    <div class="row center-block panel-body">
                          <div class="col-md-4">
                          
                        </div>
                          <div class="col-md-4">
                              <center><span class="label label-info">User ID <?php echo $usrinst->Natregno; ?></span></center>
                            <input type="hidden" name="Natregno" value="<?php echo $usrinst->Natregno; ?>" placeholder="**********">
                            <br>
                                <label for="UserName">User Name</label>
                                <input class="form-control" id="UserName" type="text" value="<?php echo $usrinst->UserName; ?>" name="UserName">
                            <label for="FirstName">First Name</label>
                                <input class="form-control" id="FirstName" type="text" value="<?php echo $usrinst->FirstName; ?>" name="FirstName">
                                <label for="Password">Password</label>
                                <input class="form-control" id="Password" type="text" value="" name="Password">
                            <label for="Parish">Roles</label>
                            <select class="form-control" name="Roles">
                                <option><?php echo $usrinst->Roles; ?></option>
                                <?php
                                foreach ($usrinst->SystemRoles as $value) {
                                    echo '<option id="">' . $value . '</option>';
                                }
                                ?>
                            </select>
                              </div>
                          <div class="col-md-4">
                             
                              </div>
                    </div>                                                
                        </div>
                <div class="modal-footer">
                    <center>
                        <button type="submit" class="btn btn-default blue" name="btn-update"><strong>Update User Profile</strong></button>
                    </center>
                          </div> 
            </div>
        </div> 
    </div>
</form>

  



