<?php

include '../../../../dbconfig.php';



/* Attempt MySQL server connection. Assuming you are running MySQL
  server with default setting (user 'root' with no password) */
//$link = mysqli_connect("localhost", "root", "", "demo");
$conn = conn();
$compterm = "";
$natregnoterm = "";
$addition = "";
$startterm = "";
$endterm = "";

if (isset($_REQUEST['compname'])) {
    // Prepare a select statement
    $compterm = "B.CompanyName='".$_REQUEST['compname']."'";
}

if (isset($_REQUEST['empid'])) {
        // Natregno
    $natregnoterm = "A.RequestorName LIKE '".$_REQUEST['empid']."'";
}

 
if (isset($_REQUEST['empid'], $_REQUEST['compname'])) {
    $addition = " AND ";
}
$sql = "SELECT * FROM prntransaction as A JOIN company as B ON A.CompanyId = B.CompanyId "
        . "JOIN prnmanage as C ON A.TranId = C.TranId WHERE ".$compterm.$addition.$natregnoterm;
//A.CloseDate IS NOT NULL AND 
if ($stmt = $conn->prepare($sql)) {
    
    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->fetchAll();
        $admin = '';
        
        // Check number of rows in the result set
        if (!empty($result)) {
            // Fetch result rows as an associative array
            echo "<tr>
                        <th>TransId</th>
                        <th>Company Name</th> 
                        <th>Requestor Name</th>
                        <th>PRN</th>
                        <th>Status</th>
                        <th>Edit</th>
                    </tr>";
            foreach ($result as $value) {
                if ($value["Status"] !='Active') {
                    $rowClass='class="active"';
                    //$buttonCode = "<td><button type='button' class='btn btn-info btn-lg edit' id=".$value["TransId"].">Closed</button></td>";
                    $buttonCode = "<td><button type='button' class='btn btn-info btn-sm edit' data-toggle='modal' disabled id=".$value["PRNumber"].">Closed</button></td>";
                }
                else { 
                    $rowClass = "class='danger'";
                    $buttonCode = "<td><button type='button' class='btn btn-info btn-sm edit' data-toggle='modal' id=".$value["PRNumber"].">Reassign</button></td>";
                }
                echo "<tr".$rowClass."><td>" . $value["TranId"] . "</td><td>" . $value["CompanyName"] . "</td>"
                        . "<td>" . $value["RequestorName"] . "</td>"
                        . "<td>" . $value["Natregno"] . "</td><td>" . $value["PRNumber"] . "</td>"
                        . "<td>" . $value["Status"] . "</td>"
                        .$buttonCode."</tr>";
            }
        } else {
            echo "<p>No payments found</p>";
        }
    }
}
// Close onnection
$conn = NULL;
?>