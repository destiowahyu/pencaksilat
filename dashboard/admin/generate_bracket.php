<?php
require_once 'bracket_logic.php';

if ($_POST) {
    $participants = intval($_POST['participants']);
    $version = intval($_POST['version']);
    $tournament_name = htmlspecialchars($_POST['tournament_name']);
    
    if ($participants < 3 || $participants > 36) {
        die("Jumlah peserta harus antara 3-36");
    }
    
    $generator = new BracketGenerator($participants, $version);
    $bracket = $generator->getBracketStructure();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bracket Tournament - <?php echo $tournament_name; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🏆 <?php echo $tournament_name ?: 'Tournament Bracket'; ?></h1>
            <p><?php echo $participants; ?> Peserta - Versi <?php echo $version; ?> - Single Elimination</p>
            <a href="index.php" class="btn-back">← Kembali</a>
        </header>

        <div class="bracket-info">
            <h4>📊 Struktur Bracket</h4>
            <?php if ($version == 1): ?>
                <?php if ($participants == 3): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 1 vs BYE (Auto Advance)</p>
                    <p>• Match 2: Peserta 2 vs Peserta 3</p>
                <?php elseif ($participants == 5): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 4 vs Peserta 5</p>
                    <p>• Peserta 1, 2, 3 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Peserta 2</p>
                    <p>• Peserta 3 vs Pemenang (Peserta 4 vs Peserta 5)</p>
                <?php elseif ($participants == 6): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 2 vs Peserta 3</p>
                    <p>• Match 2: Peserta 5 vs Peserta 6</p>
                    <p>• Peserta 1, 4 mendapatkan BYE (Auto Advance)</p>
                <?php elseif ($participants == 7): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 2 vs Peserta 3</p>
                    <p>• Match 2: Peserta 4 vs Peserta 5</p>
                    <p>• Match 3: Peserta 6 vs Peserta 7</p>
                    <p>• Peserta 1 mendapatkan BYE (Auto Advance)</p>
                <?php elseif ($participants == 9): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 8 vs Peserta 9</p>
                    <p>• Peserta 1-7 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Peserta 2</p>
                    <p>• Peserta 3 vs Peserta 4</p>
                    <p>• Peserta 5 vs Peserta 6</p>
                    <p>• Peserta 7 vs Pemenang (Peserta 8 vs Peserta 9)</p>
                <?php elseif ($participants == 10): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 4 vs Peserta 5</p>
                    <p>• Match 2: Peserta 9 vs Peserta 10</p>
                    <p>• Peserta 1, 2, 3, 6, 7, 8 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Peserta 2</p>
                    <p>• Peserta 3 vs Pemenang (Peserta 4 vs Peserta 5)</p>
                    <p>• Peserta 6 vs Peserta 7</p>
                    <p>• Peserta 8 vs Pemenang (Peserta 9 vs Peserta 10)</p>
                <?php elseif ($participants == 11): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 4 vs Peserta 5</p>
                    <p>• Match 2: Peserta 7 vs Peserta 8</p>
                    <p>• Match 3: Peserta 10 vs Peserta 11</p>
                    <p>• Peserta 1, 2, 3, 6, 9 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Peserta 2</p>
                    <p>• Peserta 3 vs Pemenang (Peserta 4 vs Peserta 5)</p>
                    <p>• Peserta 6 vs Pemenang (Peserta 7 vs Peserta 8)</p>
                    <p>• Peserta 9 vs Pemenang (Peserta 10 vs Peserta 11)</p>
                <?php elseif ($participants == 12): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 2 vs Peserta 3</p>
                    <p>• Match 2: Peserta 5 vs Peserta 6</p>
                    <p>• Match 3: Peserta 8 vs Peserta 9</p>
                    <p>• Match 4: Peserta 11 vs Peserta 12</p>
                    <p>• Peserta 1, 4, 7, 10 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Pemenang (Peserta 2 vs Peserta 3)</p>
                    <p>• Peserta 4 vs Pemenang (Peserta 5 vs Peserta 6)</p>
                    <p>• Peserta 7 vs Pemenang (Peserta 8 vs Peserta 9)</p>
                    <p>• Peserta 10 vs Pemenang (Peserta 11 vs Peserta 12)</p>
                <?php elseif ($participants == 13): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 2 vs Peserta 3</p>
                    <p>• Match 2: Peserta 5 vs Peserta 6</p>
                    <p>• Match 3: Peserta 8 vs Peserta 9</p>
                    <p>• Match 4: Peserta 10 vs Peserta 11</p>
                    <p>• Match 5: Peserta 12 vs Peserta 13</p>
                    <p>• Peserta 1, 4, 7 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Pemenang (Peserta 2 vs Peserta 3)</p>
                    <p>• Peserta 4 vs Pemenang (Peserta 5 vs Peserta 6)</p>
                    <p>• Peserta 7 vs Pemenang (Peserta 8 vs Peserta 9)</p>
                    <p>• Pemenang (Peserta 10 vs Peserta 11) vs Pemenang (Peserta 12 vs Peserta 13)</p>
                <?php elseif ($participants == 14): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 2 vs Peserta 3</p>
                    <p>• Match 2: Peserta 4 vs Peserta 5</p>
                    <p>• Match 3: Peserta 6 vs Peserta 7</p>
                    <p>• Match 4: Peserta 9 vs Peserta 10</p>
                    <p>• Match 5: Peserta 11 vs Peserta 12</p>
                    <p>• Match 6: Peserta 13 vs Peserta 14</p>
                    <p>• Peserta 1, 8 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Pemenang (Peserta 2 vs Peserta 3)</p>
                    <p>• Pemenang (Peserta 4 vs Peserta 5) vs Pemenang (Peserta 6 vs Peserta 7)</p>
                    <p>• Peserta 8 vs Pemenang (Peserta 9 vs Peserta 10)</p>
                    <p>• Pemenang (Peserta 11 vs Peserta 12) vs Pemenang (Peserta 13 vs Peserta 14)</p>
                <?php elseif ($participants == 15): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 2 vs Peserta 3</p>
                    <p>• Match 2: Peserta 4 vs Peserta 5</p>
                    <p>• Match 3: Peserta 6 vs Peserta 7</p>
                    <p>• Match 4: Peserta 8 vs Peserta 9</p>
                    <p>• Match 5: Peserta 10 vs Peserta 11</p>
                    <p>• Match 6: Peserta 12 vs Peserta 13</p>
                    <p>• Match 7: Peserta 14 vs Peserta 15</p>
                    <p>• Peserta 1 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Pemenang (Peserta 2 vs Peserta 3)</p>
                    <p>• Pemenang (Peserta 4 vs Peserta 5) vs Pemenang (Peserta 6 vs Peserta 7)</p>
                    <p>• Pemenang (Peserta 8 vs Peserta 9) vs Pemenang (Peserta 10 vs Peserta 11)</p>
                    <p>• Pemenang (Peserta 12 vs Peserta 13) vs Pemenang (Peserta 14 vs Peserta 15)</p>
                <?php elseif ($participants == 17): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 16 vs Peserta 17</p>
                    <p>• Peserta 1-15 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Peserta 2</p>
                    <p>• Peserta 3 vs Peserta 4</p>
                    <p>• Peserta 5 vs Peserta 6</p>
                    <p>• Peserta 7 vs Peserta 8</p>
                    <p>• Peserta 9 vs Peserta 10</p>
                    <p>• Peserta 11 vs Peserta 12</p>
                    <p>• Peserta 13 vs Peserta 14</p>
                    <p>• Peserta 15 vs Pemenang (Peserta 16 vs Peserta 17)</p>
                <?php elseif ($participants == 18): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 8 vs Peserta 9</p>
                    <p>• Match 2: Peserta 17 vs Peserta 18</p>
                    <p>• Peserta 1-7 dan Peserta 10-16 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Peserta 2</p>
                    <p>• Peserta 3 vs Peserta 4</p>
                    <p>• Peserta 5 vs Peserta 6</p>
                    <p>• Peserta 7 vs Pemenang (Peserta 8 vs Peserta 9)</p>
                    <p>• Peserta 10 vs Peserta 11</p>
                    <p>• Peserta 12 vs Peserta 13</p>
                    <p>• Peserta 14 vs Peserta 15</p>
                    <p>• Peserta 16 vs Pemenang (Peserta 17 vs Peserta 18)</p>
                <?php elseif ($participants == 19): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 8 vs Peserta 9</p>
                    <p>• Match 2: Peserta 13 vs Peserta 14</p>
                    <p>• Match 3: Peserta 18 vs Peserta 19</p>
                    <p>• Peserta 1-7, Peserta 10-12, Peserta 15-17 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Peserta 2</p>
                    <p>• Peserta 3 vs Peserta 4</p>
                    <p>• Peserta 5 vs Peserta 6</p>
                    <p>• Peserta 7 vs Pemenang (Peserta 8 vs Peserta 9)</p>
                    <p>• Peserta 10 vs Peserta 11</p>
                    <p>• Peserta 12 vs Pemenang (Peserta 13 vs Peserta 14)</p>
                    <p>• Peserta 15 vs Peserta 16</p>
                    <p>• Peserta 17 vs Pemenang (Peserta 18 vs Peserta 19)</p>
                <?php elseif ($participants == 20): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 4 vs Peserta 5</p>
                    <p>• Match 2: Peserta 9 vs Peserta 10</p>
                    <p>• Match 3: Peserta 14 vs Peserta 15</p>
                    <p>• Match 4: Peserta 19 vs Peserta 20</p>
                    <p>• Peserta 1, 2, 3, 6, 7, 8, 11, 12, 13, 16, 17, 18 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Peserta 2</p>
                    <p>• Peserta 3 vs Pemenang (Peserta 4 vs Peserta 5)</p>
                    <p>• Peserta 6 vs Peserta 7</p>
                    <p>• Peserta 8 vs Pemenang (Peserta 9 vs Peserta 10)</p>
                    <p>• Peserta 11 vs Peserta 12</p>
                    <p>• Peserta 13 vs Pemenang (Peserta 14 vs Peserta 15)</p>
                    <p>• Peserta 16 vs Peserta 17</p>
                    <p>• Peserta 18 vs Pemenang (Peserta 19 vs Peserta 20)</p>
                <?php elseif ($participants == 21): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 4 vs Peserta 5</p>
                    <p>• Match 2: Peserta 9 vs Peserta 10</p>
                    <p>• Match 3: Peserta 14 vs Peserta 15</p>
                    <p>• Match 4: Peserta 17 vs Peserta 18</p>
                    <p>• Match 5: Peserta 20 vs Peserta 21</p>
                    <p>• Peserta 1, 2, 3, 6, 7, 8, 11, 12, 13, 16, 19 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Peserta 2</p>
                    <p>• Peserta 3 vs Pemenang (Peserta 4 vs Peserta 5)</p>
                    <p>• Peserta 6 vs Pemenang (Peserta 7 vs Peserta 8)</p>
                    <p>• Peserta 9 vs Pemenang (Peserta 10 vs Peserta 11)</p>
                    <p>• Peserta 11 vs Peserta 12</p>
                    <p>• Peserta 13 vs Pemenang (Peserta 14 vs Peserta 15)</p>
                    <p>• Peserta 16 vs Pemenang (Peserta 17 vs Peserta 18)</p>
                    <p>• Peserta 19 vs Pemenang (Peserta 20 vs Peserta 21)</p>
                <?php elseif ($participants == 22): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 4 vs Peserta 5</p>
                    <p>• Match 2: Peserta 7 vs Peserta 8</p>
                    <p>• Match 3: Peserta 10 vs Peserta 11</p>
                    <p>• Match 4: Peserta 15 vs Peserta 16</p>
                    <p>• Match 5: Peserta 18 vs Peserta 19</p>
                    <p>• Match 6: Peserta 21 vs Peserta 22</p>
                    <p>• Peserta 1, 2, 3, 6, 9, 12, 13, 14, 17, 20 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Peserta 2</p>
                    <p>• Peserta 3 vs Pemenang (Peserta 4 vs Peserta 5)</p>
                    <p>• Peserta 6 vs Pemenang (Peserta 7 vs Peserta 8)</p>
                    <p>• Peserta 9 vs Pemenang (Peserta 10 vs Peserta 11)</p>
                    <p>• Peserta 12 vs Peserta 13</p>
                    <p>• Peserta 14 vs Pemenang (Peserta 15 vs Peserta 16)</p>
                    <p>• Peserta 17 vs Pemenang (Peserta 18 vs Peserta 19)</p>
                    <p>• Peserta 20 vs Pemenang (Peserta 21 vs Peserta 22)</p>
                <?php elseif ($participants == 23): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 4 vs Peserta 5</p>
                    <p>• Match 2: Peserta 7 vs Peserta 8</p>
                    <p>• Match 3: Peserta 10 vs Peserta 11</p>
                    <p>• Match 4: Peserta 13 vs Peserta 14</p>
                    <p>• Match 5: Peserta 16 vs Peserta 17</p>
                    <p>• Match 6: Peserta 19 vs Peserta 20</p>
                    <p>• Match 7: Peserta 22 vs Peserta 23</p>
                    <p>• Peserta 1, 2, 3, 6, 9, 12, 15, 18, 21 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Peserta 2</p>
                    <p>• Peserta 3 vs Pemenang (Peserta 4 vs Peserta 5)</p>
                    <p>• Peserta 6 vs Pemenang (Peserta 7 vs Peserta 8)</p>
                    <p>• Peserta 9 vs Pemenang (Peserta 10 vs Peserta 11)</p>
                    <p>• Peserta 12 vs Pemenang (Peserta 13 vs Peserta 14)</p>
                    <p>• Peserta 15 vs Pemenang (Peserta 16 vs Peserta 17)</p>
                    <p>• Peserta 18 vs Pemenang (Peserta 19 vs Peserta 20)</p>
                    <p>• Peserta 21 vs Pemenang (Peserta 22 vs Peserta 23)</p>
                <?php elseif ($participants == 24): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 2 vs Peserta 3</p>
                    <p>• Match 2: Peserta 5 vs Peserta 6</p>
                    <p>• Match 3: Peserta 8 vs Peserta 9</p>
                    <p>• Match 4: Peserta 11 vs Peserta 12</p>
                    <p>• Match 5: Peserta 14 vs Peserta 15</p>
                    <p>• Match 6: Peserta 17 vs Peserta 18</p>
                    <p>• Match 7: Peserta 20 vs Peserta 21</p>
                    <p>• Match 8: Peserta 23 vs Peserta 24</p>
                    <p>• Peserta 1, 4, 7, 10, 13, 16, 19, 22 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Pemenang (Peserta 2 vs Peserta 3)</p>
                    <p>• Peserta 4 vs Pemenang (Peserta 5 vs Peserta 6)</p>
                    <p>• Peserta 7 vs Pemenang (Peserta 8 vs Peserta 9)</p>
                    <p>• Peserta 10 vs Pemenang (Peserta 11 vs Peserta 12)</p>
                    <p>• Peserta 13 vs Pemenang (Peserta 14 vs Peserta 15)</p>
                    <p>• Peserta 16 vs Pemenang (Peserta 17 vs Peserta 18)</p>
                    <p>• Peserta 19 vs Pemenang (Peserta 20 vs Peserta 21)</p>
                    <p>• Peserta 22 vs Pemenang (Peserta 23 vs Peserta 24)</p>
                <?php elseif ($participants == 25): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 2 vs Peserta 3</p>
                    <p>• Match 2: Peserta 5 vs Peserta 6</p>
                    <p>• Match 3: Peserta 8 vs Peserta 9</p>
                    <p>• Match 4: Peserta 11 vs Peserta 12</p>
                    <p>• Match 5: Peserta 14 vs Peserta 15</p>
                    <p>• Match 6: Peserta 17 vs Peserta 18</p>
                    <p>• Match 7: Peserta 20 vs Peserta 21</p>
                    <p>• Match 8: Peserta 23 vs Peserta 24</p>
                    <p>• Match 9: Peserta 25 vs Peserta 26</p>
                    <p>• Peserta 1, 4, 7, 10, 13, 16, 19, 22 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Pemenang (Peserta 2 vs Peserta 3)</p>
                    <p>• Peserta 4 vs Pemenang (Peserta 5 vs Peserta 6)</p>
                    <p>• Peserta 7 vs Pemenang (Peserta 8 vs Peserta 9)</p>
                    <p>• Peserta 10 vs Pemenang (Peserta 11 vs Peserta 12)</p>
                    <p>• Peserta 13 vs Pemenang (Peserta 14 vs Peserta 15)</p>
                    <p>• Peserta 16 vs Pemenang (Peserta 17 vs Peserta 18)</p>
                    <p>• Peserta 19 vs Pemenang (Peserta 20 vs Peserta 21)</p>
                    <p>• Peserta 22 vs Pemenang (Peserta 23 vs Peserta 24)</p>
                    <p>• Peserta 25 vs Pemenang (Peserta 26 vs Peserta 27)</p>
                <?php elseif ($participants == 26): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 2 vs Peserta 3</p>
                    <p>• Match 2: Peserta 5 vs Peserta 6</p>
                    <p>• Match 3: Peserta 8 vs Peserta 9</p>
                    <p>• Match 4: Peserta 10 vs Peserta 11</p>
                    <p>• Match 5: Peserta 12 vs Peserta 13</p>
                    <p>• Match 6: Peserta 15 vs Peserta 16</p>
                    <p>• Match 7: Peserta 18 vs Peserta 19</p>
                    <p>• Match 8: Peserta 21 vs Peserta 22</p>
                    <p>• Match 9: Peserta 24 vs Peserta 25</p>
                    <p>• Match 10: Peserta 26 vs Peserta 27</p>
                    <p>• Peserta 1, 4, 7, 14, 17, 20, 23 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Pemenang (Peserta 2 vs Peserta 3)</p>
                    <p>• Peserta 4 vs Pemenang (Peserta 5 vs Peserta 6)</p>
                    <p>• Peserta 7 vs Pemenang (Peserta 8 vs Peserta 9)</p>
                    <p>• Pemenang (Peserta 10 vs Peserta 11) vs Pemenang (Peserta 12 vs Peserta 13)</p>
                    <p>• Peserta 14 vs Pemenang (Peserta 15 vs Peserta 16)</p>
                    <p>• Peserta 17 vs Pemenang (Peserta 18 vs Peserta 19)</p>
                    <p>• Peserta 20 vs Pemenang (Peserta 21 vs Peserta 22)</p>
                    <p>• Peserta 23 vs Pemenang (Peserta 24 vs Peserta 25)</p>
                    <p>• Peserta 26 vs Pemenang (Peserta 27 vs Peserta 28)</p>
                <?php elseif ($participants == 27): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 2 vs Peserta 3</p>
                    <p>• Match 2: Peserta 5 vs Peserta 6</p>
                    <p>• Match 3: Peserta 8 vs Peserta 9</p>
                    <p>• Match 4: Peserta 10 vs Peserta 11</p>
                    <p>• Match 5: Peserta 12 vs Peserta 13</p>
                    <p>• Match 6: Peserta 15 vs Peserta 16</p>
                    <p>• Match 7: Peserta 17 vs Peserta 18</p>
                    <p>• Match 8: Peserta 20 vs Peserta 21</p>
                    <p>• Match 9: Peserta 22 vs Peserta 23</p>
                    <p>• Match 10: Peserta 25 vs Peserta 26</p>
                    <p>• Match 11: Peserta 27 vs Peserta 28</p>
                    <p>• Peserta 1, 4, 7, 14, 19, 24 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Pemenang (Peserta 2 vs Peserta 3)</p>
                    <p>• Peserta 4 vs Pemenang (Peserta 5 vs Peserta 6)</p>
                    <p>• Peserta 7 vs Pemenang (Peserta 8 vs Peserta 9)</p>
                    <p>• Pemenang (Peserta 10 vs Peserta 11) vs Pemenang (Peserta 12 vs Peserta 13)</p>
                    <p>• Peserta 14 vs Pemenang (Peserta 15 vs Peserta 16)</p>
                    <p>• Peserta 17 vs Pemenang (Peserta 18 vs Peserta 19)</p>
                    <p>• Peserta 20 vs Pemenang (Peserta 21 vs Peserta 22)</p>
                    <p>• Peserta 23 vs Pemenang (Peserta 24 vs Peserta 25)</p>
                    <p>• Peserta 26 vs Pemenang (Peserta 27 vs Peserta 28)</p>
                <?php elseif ($participants == 28): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 2 vs Peserta 3</p>
                    <p>• Match 2: Peserta 5 vs Peserta 6</p>
                    <p>• Match 3: Peserta 8 vs Peserta 9</p>
                    <p>• Match 4: Peserta 11 vs Peserta 12</p>
                    <p>• Match 5: Peserta 15 vs Peserta 16</p>
                    <p>• Match 6: Peserta 18 vs Peserta 19</p>
                    <p>• Match 7: Peserta 21 vs Peserta 22</p>
                    <p>• Match 8: Peserta 24 vs Peserta 25</p>
                    <p>• Match 9: Peserta 27 vs Peserta 28</p>
                    <p>• Peserta 1, 4, 7, 10, 13, 14, 17, 20, 23, 26 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Pemenang (Peserta 2 vs Peserta 3)</p>
                    <p>• Pemenang (Peserta 4 vs Peserta 5) vs Pemenang (Peserta 6 vs Peserta 7)</p>
                    <p>• Peserta 8 vs Pemenang (Peserta 9 vs Peserta 10)</p>
                    <p>• Pemenang (Peserta 11 vs Peserta 12) vs Pemenang (Peserta 13 vs Peserta 14)</p>
                    <p>• Peserta 15 vs Pemenang (Peserta 16 vs Peserta 17)</p>
                    <p>• Pemenang (Peserta 18 vs Peserta 19) vs Pemenang (Peserta 20 vs Peserta 21)</p>
                    <p>• Peserta 22 vs Pemenang (Peserta 23 vs Peserta 24)</p>
                    <p>• Pemenang (Peserta 25 vs Peserta 26) vs Pemenang (Peserta 27 vs Peserta 28)</p>
                <?php elseif ($participants == 29): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 2 vs Peserta 3</p>
                    <p>• Match 2: Peserta 5 vs Peserta 6</p>
                    <p>• Match 3: Peserta 8 vs Peserta 9</p>
                    <p>• Match 4: Peserta 11 vs Peserta 12</p>
                    <p>• Match 5: Peserta 16 vs Peserta 17</p>
                    <p>• Match 6: Peserta 18 vs Peserta 19</p>
                    <p>• Match 7: Peserta 20 vs Peserta 21</p>
                    <p>• Match 8: Peserta 22 vs Peserta 23</p>
                    <p>• Match 9: Peserta 24 vs Peserta 25</p>
                    <p>• Match 10: Peserta 26 vs Peserta 27</p>
                    <p>• Match 11: Peserta 28 vs Peserta 29</p>
                    <p>• Peserta 1, 4, 7, 10, 13, 14, 15 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Pemenang (Peserta 2 vs Peserta 3)</p>
                    <p>• Pemenang (Peserta 4 vs Peserta 5) vs Pemenang (Peserta 6 vs Peserta 7)</p>
                    <p>• Peserta 8 vs Pemenang (Peserta 9 vs Peserta 10)</p>
                    <p>• Pemenang (Peserta 11 vs Peserta 12) vs Pemenang (Peserta 13 vs Peserta 14)</p>
                    <p>• Peserta 15 vs Pemenang (Peserta 16 vs Peserta 17)</p>
                    <p>• Pemenang (Peserta 18 vs Peserta 19) vs Pemenang (Peserta 20 vs Peserta 21)</p>
                    <p>• Pemenang (Peserta 22 vs Peserta 23) vs Pemenang (Peserta 24 vs Peserta 25)</p>
                    <p>• Pemenang (Peserta 26 vs Peserta 27) vs Pemenang (Peserta 28 vs Peserta 29)</p>
                <?php elseif ($participants == 30): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 2 vs Peserta 3</p>
                    <p>• Match 2: Peserta 4 vs Peserta 5</p>
                    <p>• Match 3: Peserta 6 vs Peserta 7</p>
                    <p>• Match 4: Peserta 8 vs Peserta 9</p>
                    <p>• Match 5: Peserta 10 vs Peserta 11</p>
                    <p>• Match 6: Peserta 12 vs Peserta 13</p>
                    <p>• Match 7: Peserta 14 vs Peserta 15</p>
                    <p>• Match 8: Peserta 17 vs Peserta 18</p>
                    <p>• Match 9: Peserta 19 vs Peserta 20</p>
                    <p>• Match 10: Peserta 21 vs Peserta 22</p>
                    <p>• Match 11: Peserta 23 vs Peserta 24</p>
                    <p>• Match 12: Peserta 25 vs Peserta 26</p>
                    <p>• Match 13: Peserta 27 vs Peserta 28</p>
                    <p>• Match 14: Peserta 29 vs Peserta 30</p>
                    <p>• Peserta 1, 16 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Pemenang (Peserta 2 vs Peserta 3)</p>
                    <p>• Pemenang (Peserta 4 vs Peserta 5) vs Pemenang (Peserta 6 vs Peserta 7)</p>
                    <p>• Pemenang (Peserta 8 vs Peserta 9) vs Pemenang (Peserta 10 vs Peserta 11)</p>
                    <p>• Pemenang (Peserta 12 vs Peserta 13) vs Pemenang (Peserta 14 vs Peserta 15)</p>
                    <p>• Peserta 16 vs Pemenang (Peserta 17 vs Peserta 18)</p>
                    <p>• Pemenang (Peserta 19 vs Peserta 20) vs Pemenang (Peserta 21 vs Peserta 22)</p>
                    <p>• Pemenang (Peserta 23 vs Peserta 24) vs Pemenang (Peserta 25 vs Peserta 26)</p>
                    <p>• Pemenang (Peserta 27 vs Peserta 28) vs Pemenang (Peserta 29 vs Peserta 30)</p>
                <?php elseif ($participants == 31): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 2 vs Peserta 3</p>
                    <p>• Match 2: Peserta 4 vs Peserta 5</p>
                    <p>• Match 3: Peserta 6 vs Peserta 7</p>
                    <p>• Match 4: Peserta 8 vs Peserta 9</p>
                    <p>• Match 5: Peserta 10 vs Peserta 11</p>
                    <p>• Match 6: Peserta 12 vs Peserta 13</p>
                    <p>• Match 7: Peserta 14 vs Peserta 15</p>
                    <p>• Match 8: Peserta 16 vs Peserta 17</p>
                    <p>• Match 9: Peserta 18 vs Peserta 19</p>
                    <p>• Match 10: Peserta 20 vs Peserta 21</p>
                    <p>• Match 11: Peserta 22 vs Peserta 23</p>
                    <p>• Match 12: Peserta 24 vs Peserta 25</p>
                    <p>• Match 13: Peserta 26 vs Peserta 27</p>
                    <p>• Match 14: Peserta 28 vs Peserta 29</p>
                    <p>• Match 15: Peserta 30 vs Peserta 31</p>
                    <p>• Peserta 1 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Pemenang (Peserta 2 vs Peserta 3)</p>
                    <p>• Pemenang (Peserta 4 vs Peserta 5) vs Pemenang (Peserta 6 vs Peserta 7)</p>
                    <p>• Pemenang (Peserta 8 vs Peserta 9) vs Pemenang (Peserta 10 vs Peserta 11)</p>
                    <p>• Pemenang (Peserta 12 vs Peserta 13) vs Pemenang (Peserta 14 vs Peserta 15)</p>
                    <p>• Pemenang (Peserta 16 vs Peserta 17) vs Pemenang (Peserta 18 vs Peserta 19)</p>
                    <p>• Pemenang (Peserta 20 vs Peserta 21) vs Pemenang (Peserta 22 vs Peserta 23)</p>
                    <p>• Pemenang (Peserta 24 vs Peserta 25) vs Pemenang (Peserta 26 vs Peserta 27)</p>
                    <p>• Pemenang (Peserta 28 vs Peserta 29) vs Pemenang (Peserta 30 vs Peserta 31)</p>
                <?php elseif ($participants == 32): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Semua 16 pertandingan di Round 1 adalah pertandingan reguler (tidak ada BYE).</p>
                    <p>• Contoh: Peserta 1 vs Peserta 2, Peserta 3 vs Peserta 4, ..., Peserta 31 vs Peserta 32.</p>
                <?php elseif ($participants == 33): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 32 vs Peserta 33</p>
                    <p>• Peserta 1-31 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Peserta 2</p>
                    <p>• Peserta 3 vs Peserta 4</p>
                    <p>• Peserta 5 vs Peserta 6</p>
                    <p>• Peserta 7 vs Peserta 8</p>
                    <p>• Peserta 9 vs Peserta 10</p>
                    <p>• Peserta 11 vs Peserta 12</p>
                    <p>• Peserta 13 vs Peserta 14</p>
                    <p>• Peserta 15 vs Peserta 16</p>
                    <p>• Peserta 17 vs Peserta 18</p>
                    <p>• Peserta 19 vs Peserta 20</p>
                    <p>• Peserta 21 vs Peserta 22</p>
                    <p>• Peserta 23 vs Peserta 24</p>
                    <p>• Peserta 25 vs Peserta 26</p>
                    <p>• Peserta 27 vs Peserta 28</p>
                    <p>• Peserta 29 vs Peserta 30</p>
                    <p>• Peserta 31 vs Pemenang (Peserta 32 vs Peserta 33)</p>
                <?php elseif ($participants == 34): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 16 vs Peserta 17</p>
                    <p>• Match 2: Peserta 33 vs Peserta 34</p>
                    <p>• Peserta 1-15 dan Peserta 18-32 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Peserta 2</p>
                    <p>• Peserta 3 vs Peserta 4</p>
                    <p>• Peserta 5 vs Peserta 6</p>
                    <p>• Peserta 7 vs Peserta 8</p>
                    <p>• Peserta 9 vs Peserta 10</p>
                    <p>• Peserta 11 vs Peserta 12</p>
                    <p>• Peserta 13 vs Peserta 14</p>
                    <p>• Peserta 15 vs Pemenang (Peserta 16 vs Peserta 17)</p>
                    <p>• Peserta 18 vs Peserta 19</p>
                    <p>• Peserta 20 vs Peserta 21</p>
                    <p>• Peserta 22 vs Peserta 23</p>
                    <p>• Peserta 24 vs Peserta 25</p>
                    <p>• Peserta 26 vs Peserta 27</p>
                    <p>• Peserta 28 vs Peserta 29</p>
                    <p>• Peserta 30 vs Peserta 31</p>
                    <p>• Peserta 32 vs Pemenang (Peserta 33 vs Peserta 34)</p>
                <?php elseif ($participants == 35): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 16 vs Peserta 17</p>
                    <p>• Match 2: Peserta 25 vs Peserta 26</p>
                    <p>• Match 3: Peserta 34 vs Peserta 35</p>
                    <p>• Peserta 1-15, Peserta 18-24, Peserta 27-33 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Peserta 2</p>
                    <p>• Peserta 3 vs Peserta 4</p>
                    <p>• Peserta 5 vs Peserta 6</p>
                    <p>• Peserta 7 vs Peserta 8</p>
                    <p>• Peserta 9 vs Peserta 10</p>
                    <p>• Peserta 11 vs Peserta 12</p>
                    <p>• Peserta 13 vs Peserta 14</p>
                    <p>• Peserta 15 vs Pemenang (Peserta 16 vs Peserta 17)</p>
                    <p>• Peserta 18 vs Peserta 19</p>
                    <p>• Peserta 20 vs Peserta 21</p>
                    <p>• Peserta 22 vs Peserta 23</p>
                    <p>• Peserta 24 vs Pemenang (Peserta 25 vs Peserta 26)</p>
                    <p>• Peserta 27 vs Peserta 28</p>
                    <p>• Peserta 29 vs Peserta 30</p>
                    <p>• Peserta 31 vs Peserta 32</p>
                    <p>• Peserta 33 vs Pemenang (Peserta 34 vs Peserta 35)</p>
                <?php elseif ($participants == 36): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 8 vs Peserta 9</p>
                    <p>• Match 2: Peserta 17 vs Peserta 18</p>
                    <p>• Match 3: Peserta 26 vs Peserta 27</p>
                    <p>• Match 4: Peserta 35 vs Peserta 36</p>
                    <p>• Peserta 1-7, Peserta 10-16, Peserta 19-25, Peserta 28-34 mendapatkan BYE (Auto Advance)</p>
                    <p><strong>Round 2:</strong></p>
                    <p>• Peserta 1 vs Peserta 2</p>
                    <p>• Peserta 3 vs Peserta 4</p>
                    <p>• Peserta 5 vs Peserta 6</p>
                    <p>• Peserta 7 vs Pemenang (Peserta 8 vs Peserta 9)</p>
                    <p>• Peserta 10 vs Peserta 11</p>
                    <p>• Peserta 12 vs Peserta 13</p>
                    <p>• Peserta 14 vs Peserta 15</p>
                    <p>• Peserta 16 vs Pemenang (Peserta 17 vs Peserta 18)</p>
                    <p>• Peserta 19 vs Peserta 20</p>
                    <p>• Peserta 21 vs Peserta 22</p>
                    <p>• Peserta 23 vs Peserta 24</p>
                    <p>• Peserta 25 vs Pemenang (Peserta 26 vs Peserta 27)</p>
                    <p>• Peserta 28 vs Peserta 29</p>
                    <p>• Peserta 30 vs Peserta 31</p>
                    <p>• Peserta 32 vs Peserta 33</p>
                    <p>• Peserta 34 vs Pemenang (Peserta 35 vs Peserta 36)</p>
                <?php else: ?>
                    <p>Total Rounds: <?php echo ceil(log($participants, 2)); ?></p>
                    <p>Jumlah BYE: <?php echo pow(2, ceil(log($participants, 2))) - $participants; ?></p>
                    <p><strong>Penjelasan:</strong> Untuk versi 2, BYE ditempatkan di akhir babak pertama. Jika jumlah peserta ganjil, bracket bagian atas akan memiliki jumlah peserta lebih banyak.</p>
                <?php endif; ?>
            <?php elseif ($version == 2): ?>
                <?php if ($participants == 5): ?>
                    <p><strong>Round 1:</strong></p>
                    <p>• Match 1: Peserta 1 vs Peserta 2</p>
                    <p>• Match 2: Peserta 3 vs BYE (Auto Advance)</p>
                    <p>• Peserta 4, 5 mendapatkan BYE (Auto Advance) dan langsung lolos ke Round 2.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="bracket-container">
            <div class="bracket" id="bracket">
                <?php foreach ($bracket as $roundName => $matches): ?>
                    <div class="round">
                        <h3><?php echo $roundName; ?></h3>
                        <?php foreach ($matches as $matchIndex => $match): ?>
                            <div class="match" data-round="<?php echo $roundName; ?>" data-match="<?php echo $matchIndex; ?>">
                                <div class="match-number">Match <?php echo $matchIndex + 1; ?></div>
                                <div class="participant <?php echo $match['player1']['id'] === 'bye' ? 'bye' : ''; ?> <?php echo ($match['player1']['id'] !== 'bye' && $match['player2']['id'] === 'bye') ? 'auto-advance' : ''; ?>" 
                                     onclick="selectWinner(this, '<?php echo $roundName; ?>', <?php echo $matchIndex; ?>, 1)">
                                    <span class="participant-name"><?php echo $match['player1']['name']; ?></span>
                                    <span class="participant-score">0</span>
                                </div>
                                <div class="participant <?php echo $match['player2']['id'] === 'bye' ? 'bye' : ''; ?>"
                                     onclick="selectWinner(this, '<?php echo $roundName; ?>', <?php echo $matchIndex; ?>, 2)">
                                    <span class="participant-name"><?php echo $match['player2']['name']; ?></span>
                                    <span class="participant-score">0</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="controls">
            <button onclick="resetBracket()" class="btn-reset">Reset Bracket</button>
            <button onclick="printBracket()" class="btn-print">Print Bracket</button>
        </div>
    </div>

    <script src="bracket.js"></script>
</body>
</html>

<style>
.btn-back {
    display: inline-block;
    background: #667eea;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 8px;
    margin-top: 15px;
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: #5a67d8;
    transform: translateY(-1px);
}

.controls {
    text-align: center;
    margin: 20px 0;
}

.btn-reset, .btn-print {
    background: #e53e3e;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    margin: 0 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-print {
    background: #38a169;
}

.btn-reset:hover {
    background: #c53030;
}

.btn-print:hover {
    background: #2f855a;
}
</style>
