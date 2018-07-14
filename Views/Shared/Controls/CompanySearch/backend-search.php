<?php

include '../../../../dbconfig.php';
$type = $_GET['type'];
$conn = conn();


if (isset($_REQUEST['term'])) {
    // Prepare a select statement
    if ($type == 'name') {
        $sql = 'SELECT * FROM company WHERE CompanyName LIKE ? AND DelFlg="N"';
    } else
    if ($type == 'companyid') {
        $sql = 'SELECT * FROM company WHERE CompanyId LIKE ? AND DelFlg="N"';
    }

    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        // Set parameters
        $param_term = '%' . $_REQUEST['term'] . '%';
        $stmt->bindParam(1, $param_term, PDO::PARAM_STR);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $result = $stmt->fetchAll();

            // Check number of rows in the result set
            if (!empty($result)) {
                // Fetch result rows as an associative array
                foreach ($result as $value) {
                    echo "<p><strong>" . $value["CompanyName"] . "</strong></p>";
                }
            } else {
                echo "<p>No matches found</p>";
            }
        } else {
            echo "ERROR: Not able to execute $sql. " . mysqli_error($conn);
        }
    }

    // Close statement

    $conn = NULL;
}

// close connection

$conn = NULL;
?>