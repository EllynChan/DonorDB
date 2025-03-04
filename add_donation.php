<?php
include 'db_connect.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get donor ID from query string
if (isset($_GET['donor_id'])) {
    $donor_id = $_GET['donor_id'];

    // Fetch donor info based on the donor ID
    $sql = "SELECT * FROM Donors WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $donor_id);  // "i" is for integer
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $donor = $result->fetch_assoc();
        // Display the donor's information (pre-fill the form)
        echo "<h3>Add a donation for " . htmlspecialchars($donor['first_name']) . " " . htmlspecialchars($donor['last_name']) . "</h3>";
    } else {
        echo "Donor not found.";
    }
} else {
    echo "No donor ID provided.";
}
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $donor_id = $_POST['donor_id'];
    $amount = $_POST['amount'];
    $donation_date = $_POST['donation_date'];
    $donation_type = $_POST['donation_type'];
    $payment_method = $_POST['payment_method'];
    $event = $_POST['event'];
    $notes = $_POST['donation_notes'];

    // Insert donation into donations table
    $sql = "INSERT INTO Donations (donor_id, donation_date, donation_type, event, amount, payment_method, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssdss", $donor_id, $donation_date, $donation_type, $event, $amount, $payment_method, $notes);
    $stmt->execute();

    // Update the total donated amount for the donor
    $sql_update = "UPDATE Donors SET total_donation = total_donation + ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("di", $amount, $donor_id);
    $stmt_update->execute();

    // Update largest donation parameter
    $sql_get_largest = "SELECT largest_donation FROM Donors WHERE id = ?";
    $stmt_get_largest = $conn->prepare($sql_get_largest);
    $stmt_get_largest->bind_param("i", $donor_id); 
    $stmt_get_largest->execute();
    $stmt_get_largest->bind_result($largest_donation);
    $stmt_get_largest->fetch();
    $stmt_get_largest->close();
    if ($amount > $largest_donation) { 
        $sql_update_largest = "UPDATE Donors SET largest_donation = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update_largest);
        $stmt_update->bind_param("di", $amount, $donor_id); 
        $stmt_update->execute();
        $stmt_update->close(); 
    }

    // Update average donation parameter
    $sql_get_total = "SELECT total_donation FROM Donors WHERE id = ?";
    $stmt_get_total = $conn->prepare($sql_get_total);
    $stmt_get_total->bind_param("i", $donor_id);
    $stmt_get_total->execute();
    $stmt_get_total->bind_result($total_donation);
    $stmt_get_total->fetch();
    $stmt_get_total->close();

    $sql_count_donations = "SELECT COUNT(*) FROM Donations WHERE donor_id = ?";
    $stmt_count = $conn->prepare($sql_count_donations);
    $stmt_count->bind_param("i", $donor_id);
    $stmt_count->execute();
    $stmt_count->bind_result($donation_count);
    $stmt_count->fetch();
    $stmt_count->close();

    $average_donation = ($donation_count > 0) ? ($total_donation / $donation_count) : 0;

    $sql_update_avg = "UPDATE Donors SET average_donation = ? WHERE id = ?";
    $stmt_update_avg = $conn->prepare($sql_update_avg);
    $stmt_update_avg->bind_param("di", $average_donation, $donor_id);
    $stmt_update_avg->execute();
    $stmt_update_avg->close();

    // Update last donation date
    $sql_get_latest_date = "SELECT last_donation_date FROM Donors WHERE id = ?";
    $stmt_get_latest = $conn->prepare($sql_get_latest_date);
    $stmt_get_latest->bind_param("i", $donor_id);
    $stmt_get_latest->execute();
    $stmt_get_latest->bind_result($last_donation_date);
    $stmt_get_latest->fetch();
    $stmt_get_latest->close();
    if ($last_donation_date === null || $donation_date > $last_donation_date) {
        $sql_update_last_date = "UPDATE Donors SET last_donation_date = ? WHERE id = ?";
        $stmt_update_date = $conn->prepare($sql_update_last_date);
        $stmt_update_date->bind_param("si", $donation_date, $donor_id);
        $stmt_update_date->execute();
        $stmt_update_date->close();
    }

    // Redirect or display success message
    echo "Donation added successfully!";
}

function processStatements($sql, $donor_id) {
    $sql_get_latest_date = "SELECT last_donation_date FROM Donors WHERE id = ?";
    $stmt_get_latest = $conn->prepare($sql_get_latest_date);
    $stmt_get_latest->bind_param("i", $donor_id);
    $stmt_get_latest->execute();
    $stmt_get_latest->bind_result($last_donation_date);
    $stmt_get_latest->fetch();
    $stmt_get_latest->close();
    
    return $last_donation_date;
}

?>

<!-- Form to add a donation -->
<form method="POST">
    <input type="hidden" name="donor_id" value="<?php echo $donor_id; ?>">
    <label for="amount">Donation Amount:</label>
    <input type="number" id="amount" name="amount" step="0.01">
    <br><br>

    <label for="donation_date">Date of Donation:</label>
    <input type="date" id="donation_date" name="donation_date" value="<?php echo date('Y-m-d'); ?>" required>
    <br><br>

    <label for="donation_type">Donation Type:</label>
    <select id="donation_type" name="donation_type" required>
        <option value="One-time">One-time</option>
        <option value="Recurring">Recurring</option>
        <option value="In-kind">In-kind</option>
        <option value="Pledged">Pledged</option>
    </select>
    <br><br>

    <label for="payment_method">Payment Method:</label>
    <select id="payment_method" name="payment_method">
        <option value="">---</option>
        <option value="Cash">Cash</option>
        <option value="Credit Card">Credit Card</option>
        <option value="ETF">ETF</option>
        <option value="Cheque">Cheque</option>
    </select>
    <br><br>

    <label for="event">Event:</label>
    <input type="text" id="event" name="event">
    <br><br>

    <label for="donation_notes">Notes:</label>
    <input type="text" id="donation_notes" name="donation_notes">
    <br><br>

    <br><br>

    <button type="submit">Submit Donation</button>
</form>
<form action="list_donors.php">
    <button type="return">Back</button>
</form>