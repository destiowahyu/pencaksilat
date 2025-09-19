<?php
class BracketGenerator {
    private $participants;
    private $version;
    private $pesertaArr;
    public function __construct($participants, $version = 2, $pesertaArr = []) {
        $this->participants = $participants;
        $this->version = 2; // Paksa versi 2
        $this->pesertaArr = $pesertaArr;
    }
    
    public function generateBracket() {
        $bracket = [];
        $totalRounds = $this->calculateRounds();
        $bracket[1] = $this->initializeFirstRound();
        for ($round = 2; $round <= $totalRounds; $round++) {
            $bracket[$round] = $this->generateRound($bracket[$round - 1]);
        }
        return $bracket;
    }
    private function calculateRounds() {
        return ceil(log($this->participants, 2));
    }
    private function getSpecialCaseStructure($participants) {
        $structure = [];
        switch($participants) {
            case 5:
                $structure[] = [ 'player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2] ];
                $structure[] = [ 'player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye'] ];
                $structure[] = [ 'player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye'] ];
                $structure[] = [ 'player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => 'BYE', 'id' => 'bye'] ];
                return $structure;
            // ... (lanjutkan seluruh special case versi 2 sesuai bracket-logic.php)
            case 6:
                // Atas (Peserta 1-3) - bracket 3 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 4-6) - bracket 3 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            // ...
            case 10:
                // Atas (Peserta 1-5) - bracket 5 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 6-10) - bracket 5 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => "Peserta 7", 'id' => 7]];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 19:
                // Atas (Peserta 1-10) - bracket 10 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => "Peserta 7", 'id' => 7]];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 11-19) - bracket 9 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => "Peserta 12", 'id' => 12]];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 19", 'id' => 19], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 11:
                // Atas (Peserta 1-6) - bracket 6 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 7-11) - bracket 5 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => "Peserta 8", 'id' => 8]];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 9:
                // Atas (Peserta 1-5) - bracket 5 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 6-9) - bracket 4 versi 2 revisi
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 12:
                // Atas (Peserta 1-6) - bracket 6 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 7-12) - bracket 6 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => "Peserta 8", 'id' => 8]];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 13:
                // Atas (Peserta 1-7) - bracket 7 versi 2 revisi
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => "Peserta 4", 'id' => 4]];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 8-13) - bracket 6 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => "Peserta 12", 'id' => 12]];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 14:
                // Atas (Peserta 1-7) - bracket 7 versi 2 revisi
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => "Peserta 4", 'id' => 4]];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 8-14) - bracket 7 versi 2 revisi
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => "Peserta 13", 'id' => 13]];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 15:
                // Atas (Peserta 1-8) - bracket 8 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => "Peserta 4", 'id' => 4]];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => "Peserta 8", 'id' => 8]];
                // Bawah (Peserta 9-15) - bracket 7 versi 2 revisi
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => "Peserta 10", 'id' => 10]];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => "Peserta 12", 'id' => 12]];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => "Peserta 14", 'id' => 14]];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 16:
                // Atas (Peserta 1-8) - bracket 8 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => "Peserta 4", 'id' => 4]];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => "Peserta 8", 'id' => 8]];
                // Bawah (Peserta 9-16) - bracket 8 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => "Peserta 10", 'id' => 10]];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => "Peserta 12", 'id' => 12]];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => "Peserta 14", 'id' => 14]];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => "Peserta 16", 'id' => 16]];
                return $structure;
            case 17:
                // Atas (Peserta 1-9) - bracket 9 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 10-17) - bracket 8 versi 2 revisi
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 18:
                // Atas (Peserta 1-9) - bracket 9 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 10-18) - bracket 9 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 20:
                // Atas (Peserta 1-10) - bracket 10 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => "Peserta 7", 'id' => 7]];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 11-20) - bracket 10 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => "Peserta 12", 'id' => 12]];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => "Peserta 17", 'id' => 17]];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 19", 'id' => 19], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 20", 'id' => 20], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 21:
                // Atas (Peserta 1-11) - bracket 11 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => "Peserta 8", 'id' => 8]];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 12-21) - bracket 10 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => "Peserta 13", 'id' => 13]];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => "Peserta 18", 'id' => 18]];
                $structure[] = ['player1' => ['name' => "Peserta 19", 'id' => 19], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 20", 'id' => 20], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 21", 'id' => 21], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 22:
                // Atas (Peserta 1-11): P1 vs P2, P3 BYE, P4 vs P5, P6 BYE, P7 vs P8, P9 BYE, P10 BYE, P11 BYE
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => "Peserta 8", 'id' => 8]];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 12-22): P12 vs P13, P14 BYE, P15 vs P16, P17 BYE, P18 vs P19, P20 BYE, P21 BYE, P22 BYE
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => "Peserta 13", 'id' => 13]];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => "Peserta 16", 'id' => 16]];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => "Peserta 19", 'id' => 19]];
                $structure[] = ['player1' => ['name' => "Peserta 20", 'id' => 20], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 21", 'id' => 21], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 22", 'id' => 22], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 23:
                // Atas (Peserta 1-12) - bracket 12 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => "Peserta 8", 'id' => 8]];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 13-23) - bracket 11 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => "Peserta 14", 'id' => 14]];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => "Peserta 17", 'id' => 17]];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 19", 'id' => 19], 'player2' => ['name' => "Peserta 20", 'id' => 20]];
                $structure[] = ['player1' => ['name' => "Peserta 21", 'id' => 21], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 22", 'id' => 22], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 23", 'id' => 23], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 24:
                // Atas (Peserta 1-12) - bracket 12 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => "Peserta 8", 'id' => 8]];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 13-24) - bracket 12 versi 2
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => "Peserta 14", 'id' => 14]];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => "Peserta 17", 'id' => 17]];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 19", 'id' => 19], 'player2' => ['name' => "Peserta 20", 'id' => 20]];
                $structure[] = ['player1' => ['name' => "Peserta 21", 'id' => 21], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 22", 'id' => 22], 'player2' => ['name' => "Peserta 23", 'id' => 23]];
                $structure[] = ['player1' => ['name' => "Peserta 24", 'id' => 24], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 25:
                // Atas (Peserta 1-13)
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => "Peserta 4", 'id' => 4]];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => "Peserta 12", 'id' => 12]];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 14-25)
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => "Peserta 15", 'id' => 15]];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => "Peserta 18", 'id' => 18]];
                $structure[] = ['player1' => ['name' => "Peserta 19", 'id' => 19], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 20", 'id' => 20], 'player2' => ['name' => "Peserta 21", 'id' => 21]];
                $structure[] = ['player1' => ['name' => "Peserta 22", 'id' => 22], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 23", 'id' => 23], 'player2' => ['name' => "Peserta 24", 'id' => 24]];
                $structure[] = ['player1' => ['name' => "Peserta 25", 'id' => 25], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 26:
                // Atas (Peserta 1-13)
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => "Peserta 4", 'id' => 4]];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => "Peserta 12", 'id' => 12]];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 14-26)
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => "Peserta 15", 'id' => 15]];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => "Peserta 17", 'id' => 17]];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => "Peserta 19", 'id' => 19]];
                $structure[] = ['player1' => ['name' => "Peserta 20", 'id' => 20], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 21", 'id' => 21], 'player2' => ['name' => "Peserta 22", 'id' => 22]];
                $structure[] = ['player1' => ['name' => "Peserta 23", 'id' => 23], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 24", 'id' => 24], 'player2' => ['name' => "Peserta 25", 'id' => 25]];
                $structure[] = ['player1' => ['name' => "Peserta 26", 'id' => 26], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 27:
                // Atas (Peserta 1-14)
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => "Peserta 4", 'id' => 4]];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => "Peserta 13", 'id' => 13]];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 15-27)
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => "Peserta 16", 'id' => 16]];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => "Peserta 18", 'id' => 18]];
                $structure[] = ['player1' => ['name' => "Peserta 19", 'id' => 19], 'player2' => ['name' => "Peserta 20", 'id' => 20]];
                $structure[] = ['player1' => ['name' => "Peserta 21", 'id' => 21], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 22", 'id' => 22], 'player2' => ['name' => "Peserta 23", 'id' => 23]];
                $structure[] = ['player1' => ['name' => "Peserta 24", 'id' => 24], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 25", 'id' => 25], 'player2' => ['name' => "Peserta 26", 'id' => 26]];
                $structure[] = ['player1' => ['name' => "Peserta 27", 'id' => 27], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 28:
                // Atas (Peserta 1-14)
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => "Peserta 4", 'id' => 4]];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => "Peserta 13", 'id' => 13]];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 15-28)
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => "Peserta 16", 'id' => 16]];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => "Peserta 18", 'id' => 18]];
                $structure[] = ['player1' => ['name' => "Peserta 19", 'id' => 19], 'player2' => ['name' => "Peserta 20", 'id' => 20]];
                $structure[] = ['player1' => ['name' => "Peserta 21", 'id' => 21], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 22", 'id' => 22], 'player2' => ['name' => "Peserta 23", 'id' => 23]];
                $structure[] = ['player1' => ['name' => "Peserta 24", 'id' => 24], 'player2' => ['name' => "Peserta 25", 'id' => 25]];
                $structure[] = ['player1' => ['name' => "Peserta 26", 'id' => 26], 'player2' => ['name' => "Peserta 27", 'id' => 27]];
                $structure[] = ['player1' => ['name' => "Peserta 28", 'id' => 28], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 29:
                // Atas (Peserta 1-15)
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => "Peserta 4", 'id' => 4]];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => "Peserta 8", 'id' => 8]];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => "Peserta 10", 'id' => 10]];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => "Peserta 12", 'id' => 12]];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => "Peserta 14", 'id' => 14]];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 16-29)
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => "Peserta 17", 'id' => 17]];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => "Peserta 19", 'id' => 19]];
                $structure[] = ['player1' => ['name' => "Peserta 20", 'id' => 20], 'player2' => ['name' => "Peserta 21", 'id' => 21]];
                $structure[] = ['player1' => ['name' => "Peserta 22", 'id' => 22], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 23", 'id' => 23], 'player2' => ['name' => "Peserta 24", 'id' => 24]];
                $structure[] = ['player1' => ['name' => "Peserta 25", 'id' => 25], 'player2' => ['name' => "Peserta 26", 'id' => 26]];
                $structure[] = ['player1' => ['name' => "Peserta 27", 'id' => 27], 'player2' => ['name' => "Peserta 28", 'id' => 28]];
                $structure[] = ['player1' => ['name' => "Peserta 29", 'id' => 29], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 30:
                // Atas (Peserta 1-15)
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => "Peserta 4", 'id' => 4]];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => "Peserta 8", 'id' => 8]];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => "Peserta 10", 'id' => 10]];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => "Peserta 12", 'id' => 12]];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => "Peserta 14", 'id' => 14]];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 16-30)
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => "Peserta 17", 'id' => 17]];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => "Peserta 19", 'id' => 19]];
                $structure[] = ['player1' => ['name' => "Peserta 20", 'id' => 20], 'player2' => ['name' => "Peserta 21", 'id' => 21]];
                $structure[] = ['player1' => ['name' => "Peserta 22", 'id' => 22], 'player2' => ['name' => "Peserta 23", 'id' => 23]];
                $structure[] = ['player1' => ['name' => "Peserta 24", 'id' => 24], 'player2' => ['name' => "Peserta 25", 'id' => 25]];
                $structure[] = ['player1' => ['name' => "Peserta 26", 'id' => 26], 'player2' => ['name' => "Peserta 27", 'id' => 27]];
                $structure[] = ['player1' => ['name' => "Peserta 28", 'id' => 28], 'player2' => ['name' => "Peserta 29", 'id' => 29]];
                $structure[] = ['player1' => ['name' => "Peserta 30", 'id' => 30], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
            case 31:
                // Atas (Peserta 1-16)
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => "Peserta 4", 'id' => 4]];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => "Peserta 8", 'id' => 8]];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => "Peserta 10", 'id' => 10]];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => "Peserta 12", 'id' => 12]];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => "Peserta 14", 'id' => 14]];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => "Peserta 16", 'id' => 16]];
                // Bawah (Peserta 17-31)
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => "Peserta 18", 'id' => 18]];
                $structure[] = ['player1' => ['name' => "Peserta 19", 'id' => 19], 'player2' => ['name' => "Peserta 20", 'id' => 20]];
                $structure[] = ['player1' => ['name' => "Peserta 21", 'id' => 21], 'player2' => ['name' => "Peserta 22", 'id' => 22]];
                $structure[] = ['player1' => ['name' => "Peserta 23", 'id' => 23], 'player2' => ['name' => "Peserta 24", 'id' => 24]];
                $structure[] = ['player1' => ['name' => "Peserta 25", 'id' => 25], 'player2' => ['name' => "Peserta 26", 'id' => 26]];
                $structure[] = ['player1' => ['name' => "Peserta 27", 'id' => 27], 'player2' => ['name' => "Peserta 28", 'id' => 28]];
                $structure[] = ['player1' => ['name' => "Peserta 29", 'id' => 29], 'player2' => ['name' => "Peserta 30", 'id' => 30]];
                $structure[] = ['player1' => ['name' => "Peserta 31", 'id' => 31], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                return $structure;
        }
        return $structure;
    }
    private function initializeFirstRound() {
        $firstRound = [];
        // Hanya logic versi 2
        if (in_array($this->participants, [5, 6, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31])) {
            return $this->getSpecialCaseStructure($this->participants);
        }
        $nextPowerOf2 = pow(2, ceil(log($this->participants, 2)));
        $participantIndex = 1;
        $matches = [];
        for ($i = 0; $i < $nextPowerOf2 / 2; $i++) {
            $match = [];
            if ($participantIndex <= $this->participants) {
                $match['player1'] = ['name' => "Peserta " . $participantIndex, 'id' => $participantIndex];
                $participantIndex++;
            } else {
                $match['player1'] = ['name' => 'BYE', 'id' => 'bye'];
            }
            if ($participantIndex <= $this->participants) {
                $match['player2'] = ['name' => "Peserta " . $participantIndex, 'id' => $participantIndex];
                $participantIndex++;
            } else {
                $match['player2'] = ['name' => 'BYE', 'id' => 'bye'];
            }
            $matches[] = $match;
        }
        $firstRound = $matches;
        return $firstRound;
    }
    private function generateRound($previousRound) {
        if ($previousRound === null) return [];
        $newRound = [];
        for ($i = 0; $i < count($previousRound); $i += 2) {
            $match = [];
            $winner1 = $this->determineWinner($previousRound[$i]);
            $winner2 = isset($previousRound[$i + 1]) ? $this->determineWinner($previousRound[$i + 1]) : ['name' => 'BYE', 'id' => 'bye'];
            $match['player1'] = $winner1;
            $match['player2'] = $winner2;
            $newRound[] = $match;
        }
        return $newRound;
    }
    private function determineWinner($match) {
        if ($match['player1']['id'] !== 'bye') {
            return $match['player1'];
        } else if ($match['player2']['id'] !== 'bye') {
            return $match['player2'];
        } else {
            return ['name' => 'BYE', 'id' => 'bye'];
        }
    }
    public function getBracketStructure() {
        $bracket = $this->generateBracket();
        $structure = [];
        foreach ($bracket as $roundNum => $matches) {
            $structure["Babak $roundNum"] = $matches;
        }
        return $structure;
    }
    private function getPesertaName($id) {
        if (isset($this->pesertaArr[$id]['name'])) return $this->pesertaArr[$id]['name'];
        return "Peserta $id";
    }
}
?>