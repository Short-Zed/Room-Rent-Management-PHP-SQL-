<?php
session_start();
include 'db_connect.php';
require_once('TCPDF-main/tcpdf.php'); // Include TCPDF library

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}

// Logout functionality
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
}

$error = "";
if (isset($_POST['calculate'])) {
    if (empty($_POST['prev_unit']) || empty($_POST['today_unit']) || empty($_POST['unit_price']) || empty($_POST['room_rent'])) {
        $error = "All required fields (Previous Unit, Today Unit, Unit Price, Room Rent) must be filled!";
    } else {
        $prev_unit = $_POST['prev_unit'];
        $today_unit = $_POST['today_unit'];
        $unit_price = $_POST['unit_price'];
        $room_rent = $_POST['room_rent'];
        $additional = $_POST['additional'] ?? 0;
        $subtraction = $_POST['subtraction'] ?? 0;
        
        if ($today_unit <= $prev_unit) {
            $error = "Today Unit must be greater than Previous Unit!";
        } else {
            $total_units = $today_unit - $prev_unit;
            $electricity_bill = $total_units * $unit_price;
            $total_rent = $electricity_bill + $room_rent + $additional - $subtraction;
            
            // Insert with record_date set to current date
            $stmt = $conn->prepare("INSERT INTO rent_records (user_id, previous_unit, today_unit, unit_price, room_rent, additional, subtraction, total_rent, record_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $prev_unit, $today_unit, $unit_price, $room_rent, $additional, $subtraction, $total_rent, date('Y-m-d')]);
        }
    }
}

// Fetch records for tabs
$stmt = $conn->prepare("SELECT * FROM rent_records WHERE user_id = ? ORDER BY record_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$records = $stmt->fetchAll();

// Download All Records as PDF
if (isset($_POST['download_all'])) {
    generatePDF($records, 'all_rent_records.pdf');
}

// Download Selected Records as PDF
if (isset($_POST['download_selected']) && !empty($_POST['selected_records'])) {
    $selected_ids = $_POST['selected_records'];
    $selected_records = array_filter($records, function($record) use ($selected_ids) {
        return in_array($record['id'], $selected_ids);
    });
    generatePDF($selected_records, 'selected_rent_records.pdf');
}

// Function to generate PDF
function generatePDF($records, $filename) {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Room Rent Calculator');
    $pdf->SetTitle('Rent Records');
    $pdf->SetHeaderData('', 0, 'Rent Records', 'Generated on ' . date('Y-m-d H:i:s'));
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setFont('dejavusans', '', 12);
    $pdf->AddPage();

    $html = '<h1>Rent Records</h1><table border="1" cellpadding="5">';
    $html .= '<tr><th>Date</th><th>Previous Unit</th><th>Today Unit</th><th>Unit Price</th><th>Room Rent</th><th>Additional</th><th>Subtraction</th><th>Total Rent</th></tr>';
    foreach ($records as $record) {
        $total_units = $record['today_unit'] - $record['previous_unit'];
        $electricity_bill = $total_units * $record['unit_price'];
        $html .= "<tr>";
        $html .= "<td>{$record['record_date']}</td>";
        $html .= "<td>{$record['previous_unit']}</td>";
        $html .= "<td>{$record['today_unit']}</td>";
        $html .= "<td>₹{$record['unit_price']}</td>";
        $html .= "<td>₹{$record['room_rent']}</td>";
        $html .= "<td>₹{$record['additional']}</td>";
        $html .= "<td>₹{$record['subtraction']}</td>";
        $html .= "<td>₹{$record['total_rent']}</td>";
        $html .= "</tr>";
    }
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output($filename, 'D');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Rent Calculator</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background-color: #f0f2f5; color: #333; line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .section { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h2 { margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        .btn { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .btn:hover { background-color: #0056b3; }
        .logout-btn { background-color: #dc3545; width: auto; padding: 10px 20px; float: right; color: #fff; border: none; outline: none; border-radius: 5px; }
        .logout-btn:hover { background-color: #c82333; cursor: pointer; }
        .nav-btn { background-color: #28a745; width: auto; padding: 10px 20px; float: left; margin-right: 10px; color: #fff; border: none; outline: none; border-radius: 5px; }
        .nav-btn:hover { background-color: #218838; cursor: pointer; }
        .record-card { border: 1px solid #eee; padding: 15px; margin-bottom: 10px; border-radius: 4px; }
        .record-actions a { margin-right: 10px; text-decoration: none; color: #007bff; }
        .record-actions a:hover { text-decoration: underline; }
        .chart-container { position: relative; height: 400px; }
        .optional-note { font-style: italic; color: #666; font-size: 0.9em; }
        .error { color: red; text-align: center; margin-bottom: 15px; background-color: #ffe6e6; padding: 10px; border-radius: 4px; }
        .tabs { overflow: hidden; margin-bottom: 20px; display:flex; gap:10px; }
        .tab-button { background-color: #ddd; float: left; border: none; outline: none; cursor: pointer; padding: 10px 20px; transition: 0.3s; border-radius:5px;}
        .tab-button:hover { background-color: #ccc; }
        .tab-button.active { background-color: #007bff; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .about-us p { margin-bottom: 15px; }
        .clearfix::after { content: ""; clear: both; display: table; }
        .footer { text-align: center; padding: 10px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .footer a { color: #ff0000; font-size: 24px; text-decoration: none; }
        .footer a:hover { color: #cc0000; }
        .download-btn { background-color: #17a2b8; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px; }
        .download-btn:hover { background-color: #138496; }

        @media (max-width: 768px) {
            .container { padding: 10px; }
            .section { padding: 15px; }
            .form-group { margin-bottom: 10px; }
            .logout-btn, .nav-btn { float: none; width: 100%; margin: 5px 0; }
            .tab-button { width: 50%; }
        }
        @media (max-width: 480px) {
            .btn, .download-btn { padding: 8px 15px; }
            .record-card { padding: 10px; }
            .chart-container { height: 300px; }
            .tab-button { width: 100%; }
            .footer { padding: 8px; }
            .footer a { font-size: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="section clearfix">
            <a href="settings.php"><button class="nav-btn">Settings</button></a>
            <form method="POST" style="display: inline;">
                <button type="submit" name="logout" class="logout-btn">Logout</button>
            </form>
        </div>

        <div class="section">
            <h2>Calculate Room Rent</h2>
            <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Previous Unit</label>
                    <input type="number" name="prev_unit" required>
                </div>
                <div class="form-group">
                    <label>Today Unit</label>
                    <input type="number" name="today_unit" required>
                </div>
                <div class="form-group">
                    <label>Unit Price</label>
                    <select name="unit_price" required>
                        <option value="3">₹3</option>    
                        <option value="4">₹4</option>
                        <option value="5">₹5</option>
                        <option value="6">₹6</option>
                        <option value="7">₹7</option>
                        <option value="8">₹8</option>
                        <option value="9">₹9</option>
                        <option value="10">₹10</option>
                        <option value="11">₹11</option>
                        <option value="12">₹12</option>
                        <option value="13">₹13</option>
                        <option value="14">₹14</option>
                        <option value="15">₹15</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Room Rent</label>
                    <input type="number" name="room_rent" required>
                </div>
                <div class="form-group">
                    <label>Additional (Optional)</label>
                    <input type="number" name="additional" value="0">
                    <span class="optional-note">Defaults to 0 if not entered</span>
                </div>
                <div class="form-group">
                    <label>Subtraction (Optional)</label>
                    <input type="number" name="subtraction" value="0">
                    <span class="optional-note">Defaults to 0 if not entered</span>
                </div>
                <button type="submit" name="calculate" class="btn">Calculate Rent</button>
            </form>
        </div>

        <div class="section">
            <div class="tabs">
                <button class="tab-button active" onclick="openTab(event, 'records')">Records</button>
                <button class="tab-button" onclick="openTab(event, 'graph')">Graph</button>
            </div>

            <div id="records" class="tab-content active">
                <h2>Your Rent Records</h2>
                <form method="POST" id="recordsForm">
                    <?php
                    foreach ($records as $record) {
                        $total_units = $record['today_unit'] - $record['previous_unit'];
                        echo "<div class='record-card'>";
                        echo "<input type='checkbox' name='selected_records[]' value='{$record['id']}'>";
                        echo "<p><strong>Date:</strong> " . $record['record_date'] . "</p>";
                        echo "<p><strong>Previous Unit:</strong> " . $record['previous_unit'] . "</p>";
                        echo "<p><strong>Today Unit:</strong> " . $record['today_unit'] . "</p>";
                        echo "<p><strong>Total Units:</strong> " . $total_units . "</p>";
                        echo "<p><strong>Unit Price:</strong> ₹" . $record['unit_price'] . "</p>";
                        echo "<p><strong>Electricity Bill:</strong> ₹" . ($total_units * $record['unit_price']) . "</p>";
                        echo "<p><strong>Room Rent:</strong> ₹" . $record['room_rent'] . "</p>";
                        echo "<p><strong>Additional:</strong> ₹" . $record['additional'] . "</p>";
                        echo "<p><strong>Subtraction:</strong> ₹" . $record['subtraction'] . "</p>";
                        echo "<p><strong>Total Rent:</strong> ₹" . $record['total_rent'] . "</p>";
                        echo "<div class='record-actions'>";
                        echo "<a href='edit.php?id={$record['id']}'>Edit</a>";
                        echo "<a href='delete.php?id={$record['id']}'>Delete</a>";
                        echo "</div>";
                        echo "</div>";
                    }
                    ?>
                    <button type="submit" name="download_all" class="download-btn">Download All as PDF</button>
                    <button type="submit" name="download_selected" class="download-btn">Download Selected as PDF</button>
                </form>
            </div>

            <div id="graph" class="tab-content">
                <h2>Rent Trend</h2>
                <div class="chart-container">
                    <canvas id="rentChart"></canvas>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>About Us</h2>
            <div class="about-us">
                <p><strong>Step 1 - Who We Are:</strong> We are a small team who made this app to help people manage their room rent easily.</p>
                <p><strong>Step 2 - What We Do:</strong> This app lets you calculate your monthly rent, including electricity costs, and keeps all your records in one place.</p>
                <p><strong>Step 3 - Why We Made It:</strong> We wanted to make it simple for anyone to track their rent without confusion or extra work.</p>
                <p><strong>Step 4 - How It Helps You:</strong> You can add, edit, or delete rent details, see a graph of your rent over time, and keep everything organized.</p>
                <p><strong>Step 5 - Our Goal:</strong> Our goal is to save you time and make rent calculations stress-free!</p>
            </div>
        </div>

        <div class="footer">
            <a href="https://www.youtube.com/@Mr.ShortZed" target="_blank">
                <i class="fab fa-youtube"></i>
            </a>
        </div>
    </div>

    <script>
    function openTab(evt, tabName) {
        var i, tabcontent, tabbuttons;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].classList.remove("active");
        }
        tabbuttons = document.getElementsByClassName("tab-button");
        for (i = 0; i < tabbuttons.length; i++) {
            tabbuttons[i].classList.remove("active");
        }
        document.getElementById(tabName).classList.add("active");
        evt.currentTarget.classList.add("active");
    }

    const ctx = document.getElementById('rentChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [<?php 
                $dates = array_map(function($r) { return "'".$r['record_date']."'"; }, $records);
                echo implode(',', $dates);
            ?>],
            datasets: [{
                label: 'Total Rent (₹)',
                data: [<?php 
                    $totals = array_map(function($r) { return $r['total_rent']; }, $records);
                echo implode(',', $totals);
                ?>],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });
    </script>
</body>
</html>