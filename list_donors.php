<?php
include 'db_connect.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if filtering conditions are set and build query conditions dynamically
$donor_id = isset($_GET['donor_id']) ? $_GET['donor_id'] : ''; // set from url
$search_donor_id = isset($_GET['search_donor_id']) ? $_GET['search_donor_id'] : ''; // set from search
$donated_greater = isset($_GET['donated_greater']) ? $_GET['donated_greater'] : '';
$donated_lesser = isset($_GET['donated_lesser']) ? $_GET['donated_lesser'] : '';
$largest_greater = isset($_GET['largest_donated_greater']) ? $_GET['largest_donated_greater'] : '';
$largest_lesser = isset($_GET['largest_donated_lesser']) ? $_GET['largest_donated_lesser'] : '';
$average_greater = isset($_GET['average_donated_greater']) ? $_GET['average_donated_greater'] : '';
$average_lesser = isset($_GET['average_donated_lesser']) ? $_GET['average_donated_lesser'] : '';
$search_first_name = isset($_GET['search_first_name']) ? $_GET['search_first_name'] : '';
$search_last_name = isset($_GET['search_last_name']) ? $_GET['search_last_name'] : '';
$last_donation_earlier = isset($_GET['last_donation_earlier']) ? $_GET['last_donation_earlier'] : '';
$last_donation_later = isset($_GET['last_donation_later']) ? $_GET['last_donation_later'] : '';

$sql = "SELECT * FROM Donors WHERE 1=1";

// Initialize an array to store the parameter types and an array to store the parameters
$params = [];
$types = '';

// Check for conditions and add to the SQL query
if (!empty($search_donor_id)) {
    $sql .= " AND id = ?";
    $params[] = $search_donor_id;
    $types .= 'i';  // 'i' means integer
}

if (!empty($donor_id)) {
    $sql .= " AND id = ?";
    $params[] = $donor_id;
    $types .= 'i'; 
}

if (!empty($donated_greater)) {
    $sql .= " AND total_donation >= ?";
    $params[] = $donated_greater;
    $types .= 'd';  // 'd' means double (decimal)
}

if (!empty($donated_lesser)) {
    $sql .= " AND total_donation <= ?";
    $params[] = $donated_lesser;
    $types .= 'd'; 
}

if (!empty($largest_greater)) {
    $sql .= " AND largest_donation >= ?";
    $params[] = $largest_greater;
    $types .= 'd';  // 'd' means double (decimal)
}

if (!empty($largest_lesser)) {
    $sql .= " AND largest_donation <= ?";
    $params[] = $largest_lesser;
    $types .= 'd'; 
}

if (!empty($average_greater)) {
    $sql .= " AND average_donation >= ?";
    $params[] = $average_greater;
    $types .= 'd';  // 'd' means double (decimal)
}

if (!empty($average_lesser)) {
    $sql .= " AND average_donation <= ?";
    $params[] = $average_lesser;
    $types .= 'd'; 
}

if (!empty($search_first_name)) {
    $sql .= " AND first_name LIKE ?";
    $params[] = '%' . $search_first_name . '%';
    $types .= 's';  // 's' means string
}

if (!empty($search_last_name)) {
    $sql .= " AND last_name LIKE ?";
    $params[] = '%' . $search_last_name . '%';
    $types .= 's';  
}

if (!empty($last_donation_later)) {
    $sql .= " AND last_donation_date >= ?";
    $params[] = $last_donation_later;
    $types .= 's'; 
}

if (!empty($last_donation_earlier)) {
    $sql .= " AND last_donation_date <= ?";
    $params[] = $last_donation_earlier;
    $types .= 's'; 
}

if (array_key_exists('search_donor_opt_in', $_GET) && $_GET['search_donor_opt_in'] !== '') {
    $sql .= " AND opted_in_newsletter = ?";
    $params[] = (int) $_GET['search_donor_opt_in'];
    $types .= 'i'; 
}

if (array_key_exists('search_donor_opt_in', $_GET) && $_GET['search_donor_active'] !== '') {
    $sql .= " AND active_status = ?";
    $params[] = (int) $_GET['search_donor_active'];
    $types .= 'i';  
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

echo "<h2>List of Donors</h2>";

if ($result->num_rows > 0) {
    // Output data of each row
    echo "<table border='1'>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Total Donation</th>
            <th>Largest Donation</th>
            <th>Average Donation</th>
            <th>Last Donation Date</th>
            <th>Opted-In Newsletter</th>
            <th>Active Status</th>
            <th>Action</th>
        </tr>";

while($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>" . $row["id"] . "</td>
            <td>" . $row["title"] . "</td>
            <td>" . $row["first_name"] . "</td>
            <td>" . $row["last_name"] . "</td>
            <td>" . $row["total_donation"] . "</td>
            <td>" . $row["largest_donation"] . "</td>
            <td>" . $row["average_donation"] . "</td>
            <td>" . $row["last_donation_date"] . "</td>
            <td>" . $row["opted_in_newsletter"] . "</td>
            <td>" . $row["active_status"] . "</td>
            <td>
                <a href='donor_details.php?donor_id=" . $row['id'] . "'>More Donor Details</a><br>
                <a href='add_donation.php?donor_id=" . $row['id'] . "'>Add a Donation</a><br>
                <a href='list_donations.php?donor_id=" . $row['id'] . "'>List Donations</a><br>
                <a href='update_donor.php?donor_id=" . $row['id'] . "'>Update Donor Info</a>
            </td>
          </tr>";
}

// todo: action: See Contact Info
// todo: action: List Donations
// todo: action: Update Donor Info (have option to deactivate donor)

echo "</table>";
} else {
    echo "0 results found.";
}

$conn->close();
?>

<form action="index.php">
    <button type="return">Back</button>
</form>

<form action="list_donations.php">
    <button type="return">Back to Donations</button>
</form>