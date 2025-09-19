<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

include '../../config/config.php';

$comp_id = $_GET['id'];

// Fetch competition details
$sql_competition = "SELECT * FROM competitions WHERE id = ?";
$stmt_competition = $conn->prepare($sql_competition);
$stmt_competition->bind_param("i", $comp_id);
$stmt_competition->execute();
$result_competition = $stmt_competition->get_result();
$competition = $result_competition->fetch_assoc();
$stmt_competition->close();

// Fetch registered athletes for the competition
$sql_athletes = "SELECT ar.*, a.athlete_name, a.age, a.gender FROM athlete_registrations ar
                 JOIN athletes a ON ar.athlete_id = a.id
                 WHERE ar.competition_id = ?";
$stmt_athletes = $conn->prepare($sql_athletes);
$stmt_athletes->bind_param("i", $comp_id);
$stmt_athletes->execute();
$result_athletes = $stmt_athletes->get_result();
$athletes = array();
while ($row = $result_athletes->fetch_assoc()) {
    $athletes[] = $row;
}
$stmt_athletes->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competition: <?php echo $competition['competition_name']; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 600px;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            cursor: pointer;
        }

        .btn-action {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
        }

        .btn-edit {
            background-color: #3498db;
        }

        .btn-delete {
            background-color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Competition: <?php echo $competition['competition_name']; ?></h1>
        <p><strong>Location:</strong> <?php echo $competition['location']; ?></p>
        <p><strong>Date:</strong> <?php echo $competition['date']; ?></p>

        <h2>Registered Athletes</h2>
        <?php if (count($athletes) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Age Category</th>
                        <th>Competition Type</th>
                        <th>Competition Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($athletes as $athlete): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($athlete['athlete_name']); ?></td>
                            <td><?php echo htmlspecialchars($athlete['age']); ?></td>
                            <td><?php echo htmlspecialchars($athlete['gender']); ?></td>
                            <td><?php echo htmlspecialchars($athlete['age_category']); ?></td>
                            <td><?php echo htmlspecialchars($athlete['competition_type']); ?></td>
                            <td><?php echo htmlspecialchars($athlete['competition_category']); ?></td>
                            <td>
                                <a href="edit-pendaftaran.php?id=<?php echo $athlete['id']; ?>" 
                                   class="btn-action btn-edit" 
                                   title="Edit Pendaftaran">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn-action btn-delete" onclick="deleteAthleteRegistration(<?php echo $athlete['id']; ?>)" title="Delete Pendaftaran">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No athletes registered for this competition yet.</p>
        <?php endif; ?>

        <a href="daftar-perlombaan.php" class="button">Back to Daftar Perlombaan</a>
    </div>

    <script>
        function deleteAthleteRegistration(registrationId) {
            if (confirm("Are you sure you want to delete this registration?")) {
                window.location.href = 'delete-pendaftaran.php?id=' + registrationId;
            }
        }

        function editAthleteRegistration(registrationId, competitionId, athleteName) {
            // Redirect to edit page instead of opening modal
            window.location.href = 'edit-pendaftaran.php?id=' + registrationId;
        }
    </script>
</body>
</html>
