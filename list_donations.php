<?php
include 'db_connect.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if filtering conditions are set and build query conditions dynamically
$amount_greater = isset($_GET['amount_greater']) ? $_GET['amount_greater'] : '';
$amount_lesser = isset($_GET['amount_lesser']) ? $_GET['amount_lesser'] : '';
$search_donations_first_name = isset($_GET['search_donations_first_name']) ? $_GET['search_donations_first_name'] : '';
$search_donations_last_name = isset($_GET['search_donations_last_name']) ? $_GET['search_donations_last_name'] : '';
$search_event = isset($_GET['search_event']) ? $_GET['search_event'] : '';
$search_donation_type = isset($_GET['search_donation_type']) ? $_GET['search_donation_type'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Fetch donors from the database
$sql = "SELECT Donations.*, Donors.first_name, Donors.last_name FROM Donations JOIN Donors ON Donations.donor_id = Donors.id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($amount_greater)) {
    $sql .= " AND Donations.amount >= ?";
    $params[] = $amount_greater;
    $types .= 'd'; 
}

if (!empty($amount_lesser)) {
    $sql .= " AND Donations.amount <= ?";
    $params[] = $amount_lesser;
    $types .= 'd'; 
}

if (!empty($search_donations_first_name)) {
    $sql .= " AND Donors.first_name LIKE ?";
    $params[] = '%' . $search_donations_first_name . '%';
    $types .= 's';
}

if (!empty($search_donations_last_name)) {
    $sql .= " AND Donors.last_name LIKE ?";
    $params[] = '%' . $search_donations_last_name . '%';
    $types .= 's';
}

if (!empty($search_event)) {
    $sql .= " AND Donations.event LIKE ?";
    $params[] = '%' . $search_event . '%';  
    $types .= 's'; 
}

if (!empty($search_donation_type)) {
    $sql .= " AND Donations.donation_type = ?";
    $params[] = $search_donation_type;  
    $types .= 's'; 
}

if (!empty($date_from)) {
    $sql .= " AND Donations.donation_date >= ?";
    $params[] = $date_from;
    $types .= 's'; 
}

if (!empty($date_to)) {
    $sql .= " AND Donations.donation_date <= ?";
    $params[] = $date_to;
    $types .= 's'; 
}

if ($types) {

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    $result = $stmt->get_result();
} else {
    // If no filters are applied
    $result = $conn->query($sql);
}

if (!$result) {
    die("Query failed: " . $conn->error); // For debugging
}

echo "<h2>List of Donations</h2>";

if ($result->num_rows > 0) {
    // Output data of each row
    echo "<table border='1'>
        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Type</th>
            <th>Event</th>
            <th>Amount</th>
            <th>Payment Method</th>
            <th>Notes</th>
            <th>Donor ID</th>
            <th>Donor Name</th>
            <th>Action</th>
        </tr>";

while($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>" . $row["id"] . "</td>
            <td>" . $row["donation_date"] . "</td>
            <td>" . $row["donation_type"] . "</td>
            <td>" . $row["event"] . "</td>
            <td>" . $row["amount"] . "</td>
            <td>" . $row["payment_method"] . "</td>
            <td>" . $row["notes"] . "</td>
            <td>" . $row["donor_id"] . "</td>
            <td>" . htmlspecialchars($row["first_name"] . " " . $row["last_name"]) . "</td>
            <td>
                <a href='list_donors.php?donor_id=" . $row['donor_id'] . "'>See Donor Info</a><br>
                <a href='update_donation.php?donor_id=" . $row['id'] . "'>Update Donation</a>
            </td>
          </tr>";
}

echo "</table>";
} else {
    echo "0 results found.";
}

$conn->close();
?>

<form action="index.php">
    <button type="return">Back</button>
</form>