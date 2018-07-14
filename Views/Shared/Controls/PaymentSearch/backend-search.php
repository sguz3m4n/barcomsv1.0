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
    $natregnoterm = "concat_ws(' ',C.FirstName,C.LastName)  LIKE '".$_REQUEST['empid']."'";
}


if (isset($_REQUEST['enddate'])) {
        // Natregno
    $endterm = "DATE(A.EndDate) <='".$_REQUEST['enddate']."'";
}


if (isset($_REQUEST['startdate'])) {
        // Natregno
    $startterm = "DATE(A.StartDate) >='".$_REQUEST['startdate']."'";
}
 
if (isset($_REQUEST['empid'], $_REQUEST['compname'])) {
    $addition = " AND A.Status='Active' ";
}
$sql = "SELECT * FROM payment as A JOIN company as B ON A.CompanyId = B.CompanyId "
        . "JOIN employee as C ON A.natregno = C.natregno WHERE ".$compterm.$addition.$natregnoterm;
//A.CloseDate IS NOT NULL AND 
if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    // Set parameters
    $param_term = $_REQUEST['term'] . '%';
    $stmt->bindParam(1, $param_term, PDO::PARAM_STR);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->fetchAll();

        // Check number of rows in the result set
        if (!empty($result)) {
            // Fetch result rows as an associative array
            echo "<tr>
                        <th>Bill Form No</th>
                        <th>Company Name</th> 
                        <th>Officer First Name</th>
                        <th>Officer Last Name</th>
                        <th>Officer ID #</th>
                        <th>Hours Worked</th>
                        <th>Rate Code</th>
                        <th>Total Payment Amount</th>
                        <th>Edit</th>
                    </tr>";
            foreach ($result as $value) {
                if ($value["CloseDate"] !='') {
                    $rowClass='class="active"';
                    $buttonCode = "<td><button type='button' class='btn btn-info btn-lg edit' id=".$value["TransId"].">Closed</button></td>";
                }
                else { 
                    $rowClass = "class='danger'";
                    $buttonCode = "<td><button type='button' class='btn btn-info btn-lg edit' data-toggle='modal' id=".$value["TransId"].">Edit</button></td>";
                }
                echo "<tr".$rowClass."><td>" . $value["BillRefNo"] . "</td><td>" . $value["CompanyName"] . "</td>"
                        . "<td>" . $value["FirstName"] . "</td><td>" . $value["LastName"] . "</td>"
                        . "<td>" . $value["Natregno"] . "</td><td>" . $value["HoursWorked"] . "</td>"
                        . "<td>" . $value["RateCode"] . "</td><td>" . $value["TotalPaymentAmount"] . "</td>"
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