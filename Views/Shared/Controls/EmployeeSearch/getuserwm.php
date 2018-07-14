<!DOCTYPE html>
<?php
include '../../../../dbconfig.php';
include '../../../../Classes/Employee.php';

$q = $_GET['q'];
$conn = conn();

$sql = "SELECT * FROM employee WHERE CONCAT_WS(' ',FirstName,LastName) = '" . $q . "' AND DelFlg='N'";

$result = $conn->prepare($sql);
$result->execute();
$empinst = new BarcomModel\Employee();
//$result = mysqli_query($conn, $sql);
//while ($row = mysqli_fetch_array($result)) 
foreach ($result as $value) {
    $empinst->Natregno = $value['Natregno'];
    $empinst->Title = $value['Title'];
    $empinst->FirstName = $value['FirstName'];
    $empinst->Initial = $value['Initial'];
    $empinst->LastName = $value['LastName'];
    $empinst->AddressLine1 = $value['AddressLine1'];
    $empinst->AddressLine2 = $value['AddressLine2'];
    $empinst->AddressLine3 = $value['AddressLine3'];
    $empinst->AddressLine4 = $value['AddressLine4'];
    $empinst->Parish = $value['Parish'];
    $empinst->PostalCode = $value['PostalCode'];
    $empinst->WorkPhone = $value['WorkPhone'];
    $empinst->HomePhone = $value['HomePhone'];
    $empinst->CellNo = $value['CellNo'];
    $empinst->Ext = $value['Ext'];
    $empinst->FaxNum = $value['FaxNum'];
    $empinst->Email = $value['Email'];
    $empinst->RoleName = $value['RoleName'];
    $empinst->RateCode = $value['RateCode'];
    $empinst->NISNo = $value['NISNo'];
    $empinst->Notes = $value['Notes'];
    $empinst->Gender = $value['Gender'];
    if ($empinst->Gender == "M") {
        $Gender = 'Male';
    } else {
        $Gender = 'Female';
    }
    $empinst->DateOfBirth = $value['DateOfBirth'];
    $EmployeeAddress = $empinst->AddressLine1 . '<br>' . $empinst->AddressLine2 . '<br>' . $empinst->AddressLine3 . '<br>' . $empinst->Parish;
    $Name = $empinst->FirstName . " " . $empinst->Initial . " " . $empinst->LastName;
    $birthDate = $empinst->DateOfBirth = $value['DateOfBirth'];
    $empinst->Age = $age = (date('Y') - date('Y', strtotime($birthDate)));
    $empinst->Age = $age;
}
$conn = NULL;

$model = new \BarcomModel\Employee();
$roles = $model->GetRoles();
$parishes = $model->GetParishes();
?> 

<div class="panel panel-info">
    <div class="panel-heading">
        <center>
            <h3>
                <ul style="list-style: none">
                    <li>
                        <img src="../assets/global/img/customs logo no back.png" alt="" height="50"/>
                        <span class="label label-info"><?php echo $Name; ?></span>&nbsp;<span class="label label-info"> <?php echo $empinst->Natregno; ?></span>  
                    </li>

                    <li>
                        <span class="label label-info"><?php echo $empinst->RoleName; ?></span>       
                    </li>
                </ul>
            </h3> 
        </center>

        <ul style="list-style: none">
            <li><label>Age: </label><?php echo $empinst->Age; ?>&nbsp;&nbsp;&nbsp;<label>Gender: </label><?php echo $Gender; ?></li>
            <li><label>Address: </label><?php echo $EmployeeAddress; ?></li>
            <li><span class="glyphicon glyphicon-phone"></span><label>Mobile: </label><?php echo $empinst->CellNo; ?></li> 
            <li><span class="glyphicon glyphicon-phone-alt"></span><label>Phone: </label><?php echo $empinst->HomePhone; ?></li>
            <li><a href="mailto:<?php echo $empinst->Email; ?>"> <?php echo $empinst->Email; ?> </a></li>
        </ul>
    </div>
</div>
</br>
<center>
    <div class="row">
        <!-- Trigger the modal with a button -->
        <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal" name="btn-edit">Edit Employee</button>
    </div>  
</center>
<form action="/employee/deactivate" method="post">
    <input type="hidden" name="Natid" value="<?php echo $empinst->Natregno; ?>">
    <input type="hidden" name="Name" value="<?php echo $Name; ?>">
    <button type="submit" class="btn btn-danger btn-default pull-right col-xs-3" name="btn-delete"><strong>Delete Employee</strong></button>
</form>
<!-- Modal --> 
<form method="post">
    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">

                <!-- Modal content-->
                <div class="modal-content">
                <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Employee Details</h4>
                </div>
                <div class="modal-body">
                    <center><span class="label label-info">National Registration Number <?php echo $empinst->Natregno; ?></span></center>
                    <input type="hidden" name="Natregno" value="<?php echo $empinst->Natregno; ?>" placeholder="**********">
                    <div class="row center-block panel-body">
                        <div class="col-xs-4">
                            <label>NIS Number</label>
                            <input class="form-control" type="text" value="<?php echo $empinst->NISNo; ?>" name="NISNo" placeholder="******"  >    
                        </div>
                        <div class="col-xs-4">
                            <label>Date of Birth</label>
                            <input type = "text" class="form-control" name = "DateOfBirth" id = "DateOfBirth" required placeholder="YYYY-MM-DD" autocomplete="off" value="<?php echo $empinst->DateOfBirth; ?>">
                        </div>
                        <div class="col-xs-2">
                            <label>Gender</label>
                            <select name="Gender" class="form-control">
                                <option><?php echo $Gender; ?></option>
                                <option>Male</option>
                                <option>Female</option>
                            </select> 
                        </div> 
                    </div>
                    <div class="row center-block panel-body">
                        <div class="col-xs-6">
                            <label>Post</label>
                            <select class="form-control" name="RoleName" id="RoleName" onchange="myFunction()">
                                <option><?php echo $empinst->RoleName; ?></option>
                                <?php
                                echo $roles;
                                ?>
                            </select>
                            <input type="hidden"  name="RateCode" id="RateCode"   />                            
                        </div>
                    </div>
                    <div class="row center-block panel-body">
                          <div class="col-xs-2">
                                <label for="Title">Title</label>
                                <input class="form-control" id="Title" type="text" value="<?php echo $empinst->Title; ?>" name="Title">
                        </div>
                          <div class="col-xs-3">
                                <label for="FirstName">First Name</label>
                                <input class="form-control" type="text" value="<?php echo $empinst->FirstName; ?>" id="FirstName" name="FirstName">
                              </div>
                          <div class="col-xs-2">
                                <label for="Initial">Initial</label>
                                <input class="form-control" id="Initial" type="text" value="<?php echo $empinst->Initial; ?>" name="Initial">
                              </div>
                        <div class="col-xs-3">
                            <label for="LastName">Last Name</label>
                                <input class="form-control" id="LastName" type="text" value="<?php echo $empinst->LastName; ?>" name="LastName" >
                        </div>
                    </div> 
                    <div class="row center-block panel-body">                     
                        <div class="col-xs-4">
                            <label for="Address">Apt /Home #</label>
                            <input class="form-control" type="text" value="<?php echo $empinst->AddressLine1; ?>" name="AddressLine1"  />  
                        </div>
                        <div class="col-xs-4">
                            <label for="Address">Street</label>
                            <input class="form-control" type="text" value="<?php echo $empinst->AddressLine2; ?>" name="AddressLine2"  />
                        </div>                      
                        <div class="col-xs-4">
                            <label for="Address">District</label>
                            <input class="form-control" type="text" value="<?php echo $empinst->AddressLine3; ?>" name="AddressLine3"  />  
                        </div>
                    </div>
                    <div class="row center-block panel-body">
                        <div class="col-xs-4">
                            <label for="Parish">Parish</label>
                            <select class="form-control" name="Parish">
                                <option><?php echo $empinst->Parish; ?></option>
                                <?php
                                echo $parishes;
                                ?>
                            </select>
                        </div> 
                        <div class="col-xs-3">
                            <label for="Postal">Postal Code</label>
                            <input class="form-control" type="text" value="<?php echo $empinst->PostalCode; ?>" name="PostalCode" /> 
                        </div>
                    </div>
                    <div class="row center-block panel-body">
                        <div class="col-xs-4">
                            <label>Work Phone</label>
                            <input class="form-control" type="text" value="<?php echo $empinst->WorkPhone; ?>" name="WorkPhone" placeholder="888-8888" />   
                            <label>Ext</label>
                            <input class="form-control" type="text" value="<?php echo $empinst->Ext; ?>" name="Ext"  />  
                            <label>Fax</label>
                            <input class="form-control" type="text" value="<?php echo $empinst->FaxNum; ?>" name="FaxNum" placeholder="888-8888" /> 
                        </div>
                        <div class="col-xs-4">
                            <label>Home Phone</label>
                            <input class="form-control" type="text" value="<?php echo $empinst->HomePhone; ?>" name="HomePhone" placeholder="888-8888" />
                            <label>Mobile Phone</label>
                            <input class="form-control" type="text" value="<?php echo $empinst->CellNo; ?>" name="CellNo" placeholder="888-8888" /> 
                            <label>Email</label>
                            <input class="form-control" type="text" value="<?php echo $empinst->Email; ?>" name="Email" placeholder="example@domain"  /> 
                        </div>
                    </div>
                    <div class="row center-block panel-body">
                        <div class="col-xs-4"><label for="Notes">Notes</label>
                            <textarea class="form-control" rows="4" columns="5" id="comments" type="text" name="Notes"><?php echo $empinst->Notes; ?></textarea> 
                        </div>
                    </div>
                          <div class="modal-footer">
                        <center>
                            <button type="submit" class="btn btn-default blue" name="btn-update"><strong>Update Employee</strong></button>
                        </center>
                              </div>                      
                        </div>
            </div>
        </div> 
    </div>
</form>

  



