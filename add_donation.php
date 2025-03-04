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
    $params = [$donor_id, $donation_date, $donation_type, $event, $amount, $payment_method, $notes];
    executeUpdate($conn, $sql, $params, 'isssdss');

    // Update the total donated amount for the donor
    $sql_update = "UPDATE Donors SET total_donation = total_donation + ? WHERE id = ?";
    executeUpdate($conn, $sql_update, [$amount, $donor_id], 'di');

    // Update largest donation parameter
    $largest_donation = fetchValue($conn, "SELECT largest_donation FROM Donors WHERE id = ?", [$donor_id], 'i');
    if ($amount > $largest_donation) {
        $sql_update_largest = "UPDATE Donors SET largest_donation = ? WHERE id = ?";
        executeUpdate($conn, $sql_update_largest, [$amount, $donor_id], 'di');
    }

    // Update average donation parameter
    $total_donation = fetchValue($conn, "SELECT total_donation FROM Donors WHERE id = ?", [$donor_id], 'i');
    $sql_count_donations = "SELECT COUNT(*) FROM Donations WHERE donor_id = ?";
    $donation_count = fetchValue($conn, $sql_count_donations, [$donor_id], 'i');
    $average_donation = ($donation_count > 0) ? ($total_donation / $donation_count) : 0;
    $sql_update_avg = "UPDATE Donors SET average_donation = ? WHERE id = ?";
    executeUpdate($conn, $sql_update_avg, [$average_donation, $donor_id], 'di');

    // Update last donation date
    $last_donation_date = fetchValue($conn, "SELECT last_donation_date FROM Donors WHERE id = ?", [$donor_id], 'i');
    if ($last_donation_date === null || $donation_date > $last_donation_date) {
        $sql_update_last_date = "UPDATE Donors SET last_donation_date = ? WHERE id = ?";
        executeUpdate($conn, $sql_update_last_date, [$donation_date, $donor_id], 'si');
    }

    // Redirect or display success message
    echo "Donation added successfully!";
}

function fetchValue($conn, $sql, $params, $types) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param($types, ...$params);  
    $stmt->execute();
    $stmt->bind_result($result);
    $stmt->fetch();
    $stmt->close();

    return $result;
}

function executeUpdate($conn, $sql, $params, $types) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param($types, ...$params);  
    $stmt->execute();
    $stmt->close();
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