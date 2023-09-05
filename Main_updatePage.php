<?php
require_once('pm_ver6_operations.php');
require_once('mntlog_ver2.php');
require 'vendor/autoload.php';

// connecting to the MySql database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "maintenance";

$conn = new mysqli($servername, $username, $password, $dbname);

// checking connection:
if ($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

// // updating status if form is submitted (file: pm_operations.php.php)

// Retrieve data from table
$sql = "SELECT * FROM pm_schedule";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Maintenance Status</title>
        
        <link rel="stylesheet" type="text/css" href="styles.css">
        
    </head>
    <body>       
        <div class="top-container">            
            <!-- adding Drop-down for table selection -->
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="float: right;">
                <select name="tableSelect" onchange="this.form.submit()" class="action-form2">
                    <option value="maintenance" selected>Maintenance</option>
                    <option value="calibration">Calibration</option>
                </select>
            </form>
            <!-- adding 'Update Schedule' button
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="float: right;">
                <input type="submit" name="update_schedule" value="Update Schedule" class="action-form2">
            </form>          -->
            <!-- adding 'Log Data' button -->
            <form action="mntlog_ver2.php" method="post" style="float: right;">
                <input type="hidden" name="log_data" value="1">
                <input type="submit" value="Log Data" class="action-form2">
            </form>
            <!-- adding Logout button -->
            <form method="POST" action="logout.php" style="float: right;">
                <button type="submit" class="action-form2">Logout</button>
            </form>
            <h1>Machine Maintenance</h1>
        </div>

        <!-- adding 'Save Changes' button -->
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="submit" value="Save Changes" class="action-form2">

        <table>
            <thead>
                <tr>
                    <th style='width:2%'>S.N.</th>
                    <th style='width:17%'>Equipment</th>
                    <th style='width:15%'>Instrument Code</th>
                    <th style='width:5%'>Frequency</th>
                    <th style='width:5%'></th>
                    <th style='width:8%'>Due From</th>
                    <th style='width:8%'>Due Till</th>
                    <th style='width:8%'>Done on</th>
                    <th style='width:2.5%'>Done</th>
                    <th style='width:5%'>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["SNO"] . "</td>";
                        echo "<td><input type='text' name='equipment[" . $row["SNO"] . "]' value='" . $row["EQUIPMENT"] . "' style='width: 90%;'></td>";
                        echo "<td><input type='text' name='instrCode[" . $row["SNO"] . "]' value='" . $row["MC_CODE"] . "' style='width: 90%;'></td>";
                        $freqUnit = $row["FREQ_UNIT"];
                        $fUnit = 'Mo';
                        if($freqUnit == 'WEEKS')
                            $fUnit = 'W';
                        echo "<td><input type='number' name='frequency[" . $row["SNO"] . "]' value='" . $row["FREQ_INT"] . "' style='width: 45%;'> to</td>";                            
                        echo "<td><input type='number' name='frequency2[" . $row["SNO"] . "]' value='" . $row["FREQ_INT2"] . "' style='width: 45%;'> " . $fUnit . "</td>";
                        echo "<td><input type='date' name='dueFrom[" . $row["SNO"] . "]' value='" . $row["DUE_FROM"] . "'></td>";
                        echo "<td><input type='date' name='dueTill[" . $row["SNO"] . "]' value='" . $row["DUE_TILL"] . "'></td>";
                        echo "<td><input type='date' name='done_on[" . $row["SNO"] . "]' value='" . $row["DONE_ON"] . "'></td>";

                        // calculating
                        $currDate = new DateTime();
                        $dueFromDate = new DateTime($row["DUE_FROM"]);
                        $doneOnDate = $row["DONE_ON"];                       
                        // checking if curr date is within maintenance period
                        if($currDate >= $dueFromDate){
                            if ($doneOnDate == "0000-00-00" || $doneOnDate == null){
                                $dueInterval = $dueFromDate->diff($currDate);
                                $period = $row["FREQ_INT2"] - $row["FREQ_INT"];                            
                                if($freqUnit == "WEEKS") {
                                    if($dueInterval->days <= $period*7){
                                        $status = 'PENDING';
                                    } else {
                                        $status = 'OVERDUE';
                                    }
                                } else {
                                    if($dueInterval->m <= $period){
                                        $status = 'PENDING';
                                    } else {
                                        $status = 'OVERDUE';
                                    }
                                }
                            } else {
                                $status = 'NOT DUE';
                            }                            
                        } else {
                            $status = 'NOT DUE';
                        }

                        echo "<input type='hidden' name='checked[" . $row["SNO"] . "]' value='0'>";
                        echo "<td><input type='checkbox' name='checked[" . $row["SNO"] . "]' value='1' "
                         . ($status == 'NOT DUE' ? " disabled" : "") . "></td>";                        
                        
                        echo "<td class='$status'>" . $status . "</td>";
                                                
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No data available</td></tr>";
                }
                ?>
            </tbody>
        </table>
        </form>
    </body>
</html>

<?php
// close database connection
$conn->close();
?>
