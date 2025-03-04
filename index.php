<?php
// Database connection
include 'db_connect.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add to Donors table
    $title = $_POST['title'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $birthday = $_POST['birthday'];
    $gender = $_POST['gender'];
    $occupation = $_POST['occupation'];
    $employer = $_POST['employer'];
    $partner = $_POST['partner'];
    $preferred_name = $_POST['preferred_name'];
    $anonymous = (int) $_POST['anonymous'];
    $opt_in_newsletter = (int) $_POST['opt_in_newsletter'];
    $donor_status = (int) $_POST['donor_status'];
    $notes = $_POST['donor_notes'];

    // Insert donor into Donors table
    if (!empty($first_name) && !empty($last_name)) {

      $sql_donor = "INSERT INTO Donors (title, first_name, last_name, birthday, gender, occupation, employer, partner, preferred_donation_name, anonymous_donation, opted_in_newsletter, active_status, notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
      $donor_stmt = $conn->prepare($sql_donor);
      $donor_stmt->bind_param("sssssssssiiis", $title, $first_name, $last_name, $birthday, $gender, $occupation, $employer, $partner, $preferred_name, $anonymous, $opt_in_newsletter, $donor_status, $notes);

      if ($donor_stmt->execute()) {
          echo "Donor added successfully! <br>";

          // Add to ContactInfo table
          $donor_id = $donor_stmt->insert_id; 
          $email = $_POST['email'];
          $phone_type = $_POST['phone_type'];
          $phone = $_POST['phone'];
          $address = $_POST['address'];
          $preferred_contact = $_POST['preferred_contact'];
          $preferred_language = $_POST['preferred_language'];

          if (!empty($email) && !empty($address)) {
            $sql_contact_info = "INSERT INTO ContactInfo (donor_id, email, phone_type, phone_number, mailing_address, preferred_contact, language_preference) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
            $contact_stmt = $conn->prepare($sql_contact_info);
            $contact_stmt->bind_param("issssss", $donor_id, $email, $phone_type, $phone, $address, $preferred_contact, $preferred_language);
            
            if ($contact_stmt->execute()) {
                echo "Contact info added successfully! <br>";
            } else {
                echo "Error: " . $sql_contact_info . "<br>" . $conn->error . "<br>";
                $sql_delete = "DELETE FROM Donors WHERE id = ?";
                $delete_stmt = $conn->prepare($sql_delete);
                $delete_stmt->bind_param("i", $donor_id);
                $delete_stmt->execute();
                $delete_stmt->close();
                echo "Associated Donor is being removed <br>";
            }
          }
      } else {
          echo "Error: " . $sql_donor . "<br>" . $conn->error . "<br>";
      }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="style.css">
<div class = "main">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Form</title>
</head>
<body>
    <h1>Add a New Donor</h1>

    <!-- Form for collecting donor data -->
    <form method="POST">
        <!-- Title -->
        <label for="title">Title:</label>
        <input type="text" id="title" name="title">
        &nbsp;&nbsp;&nbsp;&nbsp;

        <!-- First Name -->
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" required>
        &nbsp;&nbsp;&nbsp;&nbsp;

        <!-- Last Name -->
        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" required>
        &nbsp;&nbsp;&nbsp;&nbsp;

        <!-- Gender -->
        <label for="gender">Gender:</label>
        <select id="gender" name="gender">
          <option value="">---</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
          <option value="Other">Other</option>
        </select>
        <br><br>

        <!-- Birthday -->
        <label for="birthday">Birthday:</label>
        <input type="date" id="birthday" name="birthday">
        &nbsp;&nbsp;&nbsp;&nbsp;

        <!-- Occupation -->
        <label for="occupation">Occupation:</label>
        <input type="text" id="occupation" name="occupation">
        &nbsp;&nbsp;&nbsp;&nbsp;

        <!-- Employer -->
        <label for="employer">Employer:</label>
        <input type="text" id="employer" name="employer">
        &nbsp;&nbsp;&nbsp;&nbsp;

        <!-- Partner -->
        <label for="partner">Partner:</label>
        <input type="text" id="partner" name="partner">
        <br><br>

        <!-- Preferred Name -->
        <label for="preferred_name">Preferred Donation Name:</label>
        <input type="text" id="preferred_name" name="preferred_name">
        &nbsp;&nbsp;&nbsp;&nbsp;

        <!-- Anonymous -->
        <label for="anonymous">Anonymous Donation:</label>
        <select id="anonymous" name="anonymous">
          <option value="0">No</option>
          <option value="1">Yes</option>
        </select>
        &nbsp;&nbsp;&nbsp;&nbsp;

        <!-- Opt in/out Newsletter -->
        <label for="opt_in_newsletter">Opt-in to Newsletters/Emails:</label>
        <select id="opt_in_newsletter" name="opt_in_newsletter">
          <option value="0">No</option>
          <option value="1">Yes</option>
        </select>
        &nbsp;&nbsp;&nbsp;&nbsp;

        <!-- Active Status -->
        <label for="donor_status">Donor Status:</label>
        <select id="donor_status" name="donor_status">
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>

        <h1>Donor Contact Information</h1>

        <!-- Email -->
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        &nbsp;&nbsp;&nbsp;&nbsp;

        <!-- Phone Type -->
        <label for="phone_type">Phone Type:</label>
        <select id="phone_type" name="phone_type">
          <option value="Home">Home</option>
          <option value="Work">Work</option>
          <option value="Mobile">Mobile</option>
        </select>
        &nbsp;&nbsp;&nbsp;&nbsp;

        <!-- Phone Number -->
        <label for="phone">Phone:</label>
        <input type="tel" id="phone" name="phone">
        &nbsp;&nbsp;&nbsp;&nbsp;

        <!-- Preferred Contact -->
        <label for="preferred_contact">Preferred Contact:</label>
        <select id="preferred_contact" name="preferred_contact">
          <option value="">---</option>
          <option value="Email">Email</option>
          <option value="Phone">Phone</option>
        </select>
        <br><br>

        <!-- Address -->
        <label for="address">Address:</label>
        <input type="address" id="address" name="address" required>
        &nbsp;&nbsp;&nbsp;&nbsp;

        <!-- Preferred Language -->
        <label for="preferred_language">Preferred Language:</label>
        <input type="text" id="preferred_language" name="preferred_language">
        &nbsp;&nbsp;&nbsp;&nbsp;

        <!-- Notes -->
        <label for="donor_notes">Notes:</label>
        <input type="text" id="donor_notes" name="donor_notes">
        <br><br>

        <!-- Submit Button -->
        <button type="submit">Add Donor</button>
    </form>
</body>
<br><br>
<body>
  <h1>Search for Donors / Add Donations to Donors</h1>
  <h3>Note that all filtering conditions are optional. If none are given, List Donors will give the entire list of donors</h3>

  <form action="list_donors.php" method="GET">
    <!-- Search by ID -->
    <label for="search_donor_id">ID:</label>
    <input type="text" id="search_donor_id" name="search_donor_id">
    &nbsp;&nbsp;&nbsp;&nbsp;

    <!-- Donation amount filter -->
    <label for="donated_greater">Total Donation Amount Greater Than:</label>
    <input type="number" id="donated_greater" name="donated_greater" step="0.01">
    &nbsp;&nbsp;&nbsp;&nbsp;

    <label for="donated_lesser">Total Donation Amount Less Than:</label>
    <input type="number" id="donated_lesser" name="donated_lesser" step="0.01">
    <br><br>

    <label for="largest_donated_greater">Largest Donation Amount Greater Than:</label>
    <input type="number" id="largest_donated_greater" name="largest_donated_greater" step="0.01">
    &nbsp;&nbsp;&nbsp;&nbsp;

    <label for="largest_donated_lesser">Largest Donation Amount Less Than:</label>
    <input type="number" id="largest_donated_lesser" name="largest_donated_lesser" step="0.01">
    <br><br>

    <label for="average_donated_greater">Average Donation Amount Greater Than:</label>
    <input type="number" id="average_donated_greater" name="average_donated_greater" step="0.01">
    &nbsp;&nbsp;&nbsp;&nbsp;

    <label for="average_donated_lesser">Average Donation Amount Less Than:</label>
    <input type="number" id="average_donated_lesser" name="average_donated_lesser" step="0.01">
    <br><br>

    <!-- Search by name -->
    <label for="search_first_name">First Name:</label>
    <input type="text" id="search_first_name" name="search_first_name">
    &nbsp;&nbsp;&nbsp;&nbsp;

    <label for="search_last_name">Last Name:</label>
    <input type="text" id="search_last_name" name="search_last_name">
    <br><br>

    <!-- Search by last donated date -->
    <label for="last_donation_earlier">Last Donation Earlier Than:</label>
    <input type="date" id="last_donation_earlier" name="last_donation_earlier" value="">
    &nbsp;&nbsp;&nbsp;&nbsp;

    <label for="last_donation_later">Last Donation Later Than:</label>
    <input type="date" id="last_donation_later" name="last_donation_later" value="">
    <br><br>

    <!-- Search by Opt in/out Newsletter -->
    <label for="search_donor_opt_in">Whether or not Opted-in to newsletter/emails:</label>
    <select id="search_donor_opt_in" name="search_donor_opt_in">
      <option value="">---</option>
      <option value="1">Opted-in</option>
      <option value="0">Opted-out</option>
    </select>
    &nbsp;&nbsp;&nbsp;&nbsp;

    <!-- Search by Active or Not -->
    <label for="search_donor_active">Whether or not active:</label>
    <select id="search_donor_active" name="search_donor_active">
      <option value="">---</option>
      <option value="1">Active</option>
      <option value="0">Inactive</option>
    </select>
    <br><br>

    <button type="query">List Donors</button>
  </form>
  
</body>
<body>
  <h1>Search for Donations</h1>
  <h3>Note that all filtering conditions are optional. If none are given, List Donations will give the entire list of donations</h3>

  <form action="list_donations.php" method="GET">
    <!-- Donation amount filter -->
    <label for="amount_greater">Donation Amount Greater Than:</label>
    <input type="number" id="amount_greater" name="amount_greater" step="0.01">
    &nbsp;&nbsp;&nbsp;&nbsp;

    <label for="amount_lesser">Donation Amount Less Than:</label>
    <input type="number" id="amount_lesser" name="amount_lesser" step="0.01">
    &nbsp;&nbsp;&nbsp;&nbsp;

    <label for="search_donation_type">Donation Type:</label>
    <select id="search_donation_type" name="search_donation_type">
      <option value="">---</option>
      <option value="One-time">One-time</option>
      <option value="Recurring">Recurring</option>
      <option value="In-kind">In-kind</option>
      <option value="Pledged">Pledged</option>
    </select>
    <br><br>

    <!-- Search by donor name -->
    <label for="search_donations_first_name">Donor First Name:</label>
    <input type="text" id="search_donations_first_name" name="search_donations_first_name">
    &nbsp;&nbsp;&nbsp;&nbsp;

    <label for="search_donations_last_name">Donor Last Name:</label>
    <input type="text" id="search_donations_last_name" name="search_donations_last_name">
    &nbsp;&nbsp;&nbsp;&nbsp;

    <!-- Search by event name -->
    <label for="search_event">Event:</label>
    <input type="text" id="search_event" name="search_event">
    <br><br>

    <!-- Search by donation date -->
    <label for="date_from">From:</label>
    <input type="date" id="date_from" name="date_from" value="">
    &nbsp;&nbsp;&nbsp;&nbsp;

    <label for="date_to">To:</label>
    <input type="date" id="date_to" name="date_to" value="">
    <br><br>

    <button type="query">List Donations</button>
  </form>
  
</body>
</div>
</html>
