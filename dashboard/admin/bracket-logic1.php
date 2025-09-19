<?php
class BracketGenerator {
    private $participants;
    private $version;
    private $pesertaArr;
    public function __construct($participants, $version = 1, $pesertaArr = []) {
        $this->participants = $participants;
        $this->version = 1; // Paksa versi 1
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
            case 3:
                $structure[] = [ 'player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye'] ];
                $structure[] = [ 'player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => "Peserta 3", 'id' => 3] ];
                break;
            case 5:
                $structure[] = [ 'player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye'] ];
                $structure[] = [ 'player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => 'BYE', 'id' => 'bye'] ];
                $structure[] = [ 'player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye'] ];
                $structure[] = [ 'player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5] ];
                break;
            case 6:
                $structure[] = [ 'player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye'] ];
                $structure[] = [ 'player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => "Peserta 3", 'id' => 3] ];
                $structure[] = [ 'player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye'] ];
                $structure[] = [ 'player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6] ];
                break;
            case 7:
                $structure[] = [ 'player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye'] ];
                $structure[] = [ 'player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => "Peserta 3", 'id' => 3] ];
                $structure[] = [ 'player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5] ];
                $structure[] = [ 'player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => "Peserta 7", 'id' => 7] ];
                break;
            case 9:
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                break;
            case 10:
                // Atas (Peserta 1-5)
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                // Bawah (Peserta 6-10)
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => "Peserta 10", 'id' => 10]];
                break;
            case 11:
                // Atas (Peserta 1-5) - bracket 5 versi 1
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                // Bawah (Peserta 6-11) - bracket 6 versi 1
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => "Peserta 8", 'id' => 8]];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                break;
            case 12:
                // Atas (Peserta 1-6) - bracket 6 versi 1
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => "Peserta 3", 'id' => 3] ];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                // Bawah (Peserta 7-12) - bracket 6 versi 1
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => "Peserta 12", 'id' => 12]];
                break;
            case 13:
                // Atas (Peserta 1-6) - bracket 6 versi 1
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => "Peserta 3", 'id' => 3]];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                // Bawah (Peserta 7-13) - bracket 7 versi 1
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => "Peserta 13", 'id' => 13]];
                break;
            case 14:
                // Atas (Peserta 1-7) - bracket 7 versi 1
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => "Peserta 3", 'id' => 3]];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => "Peserta 7", 'id' => 7]];
                // Bawah (Peserta 8-14) - bracket 7 versi 1
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => "Peserta 10", 'id' => 10]];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => "Peserta 12", 'id' => 12]];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => "Peserta 14", 'id' => 14]];
                break;
            case 15:
                // Atas (Peserta 1-7) - bracket 7 versi 1
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => "Peserta 3", 'id' => 3]];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => "Peserta 7", 'id' => 7]];
                // Bawah (Peserta 8-15) - bracket 8 versi 1
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => "Peserta 13", 'id' => 13]];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => "Peserta 15", 'id' => 15]];
                break;
            case 16:
                // Atas (Peserta 1-8) - bracket 8 versi 1
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => "Peserta 2", 'id' => 2]];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => "Peserta 4", 'id' => 4]];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => "Peserta 8", 'id' => 8]];
                // Bawah (Peserta 9-16) - bracket 8 versi 1
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => "Peserta 10", 'id' => 10]];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => "Peserta 12", 'id' => 12]];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => "Peserta 14", 'id' => 14]];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => "Peserta 16", 'id' => 16]];
                break;
            case 17:
                // Atas (Peserta 1-8): Semua lawan BYE
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 9-17): Semua BYE kecuali P16 vs P17
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => "Peserta 17", 'id' => 17]];
                break;
            case 18:
                // Atas (Peserta 1-9): P1 BYE, P2 BYE, P3 BYE, P4 BYE, P5 BYE, P6 BYE, P7 BYE, P8 vs P9
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                // Bawah (Peserta 10-18): P10 vs BYE, P11 BYE, ..., P16 BYE, P17 vs P18
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => "Peserta 18", 'id' => 18]];
                break;
            case 19:
                // Atas (Peserta 1-9): P1 BYE, ..., P7 BYE, P8 vs P9
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                // Bawah (Peserta 10-19): P11 BYE, P12 BYE, P13 vs P14, P15 BYE, P16 BYE, P17 BYE, P18 vs P19
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => "Peserta 14", 'id' => 14]];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => "Peserta 19", 'id' => 19]];
                break;
            case 20:
                // Atas (Peserta 1-10): P1 BYE, P2 BYE, P3 BYE, P4 vs P5, P6 BYE, P7 BYE, P8 BYE, P9 vs P10
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => "Peserta 10", 'id' => 10]];
                // Bawah (Peserta 11-20): P11 BYE, P12 BYE, P13 BYE, P14 vs P15, P16 BYE, P17 BYE, P18 BYE, P19 vs P20
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => "Peserta 15", 'id' => 15]];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 19", 'id' => 19], 'player2' => ['name' => "Peserta 20", 'id' => 20]];
                break;
            case 21:
                // Atas (Peserta 1-10): P1 BYE, P2 BYE, P3 BYE, P4 vs P5, P6 BYE, P7 BYE, P8 BYE, P9 vs P10
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => "Peserta 10", 'id' => 10]];
                // Bawah (Peserta 11-21): P11 BYE, P12 BYE, P13 BYE, P14 vs P15, P16 BYE, P17 vs P18, P19 BYE, P20 vs P21
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => "Peserta 15", 'id' => 15]];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => "Peserta 18", 'id' => 18]];
                $structure[] = ['player1' => ['name' => "Peserta 19", 'id' => 19], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 20", 'id' => 20], 'player2' => ['name' => "Peserta 21", 'id' => 21]];
                break;
            case 22:
                file_put_contents(__DIR__.'/debug22.txt', date('Y-m-d H:i:s')."\n", FILE_APPEND);
                // Atas (Peserta 1-11): P1 BYE, P2 BYE, P3 BYE, P4 vs P5, P6 BYE, P7 vs P8, P9 BYE, P10 vs P11
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => "Peserta 8", 'id' => 8]];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                // Bawah (Peserta 12-22): P12 BYE, P13 BYE, P14 BYE, P15 vs P16, P17 BYE, P18 vs P19, P20 BYE, P21 vs P22
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => "Peserta 16", 'id' => 16]];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => "Peserta 19", 'id' => 19]];
                $structure[] = ['player1' => ['name' => "Peserta 20", 'id' => 20], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 21", 'id' => 21], 'player2' => ['name' => "Peserta 22", 'id' => 22]];
                break;
            case 23:
                // Atas (Peserta 1-11) - bracket 11 versi 1
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 3", 'id' => 3], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => "Peserta 8", 'id' => 8]];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                // Bawah (Peserta 12-23) - bracket 12 versi 1
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => "Peserta 14", 'id' => 14]];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => "Peserta 17", 'id' => 17]];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 19", 'id' => 19], 'player2' => ['name' => "Peserta 20", 'id' => 20]];
                $structure[] = ['player1' => ['name' => "Peserta 21", 'id' => 21], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 22", 'id' => 22], 'player2' => ['name' => "Peserta 23", 'id' => 23]];
                break;
            case 24:
                // Atas (Peserta 1-12) - bracket 12 versi 1
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => "Peserta 3", 'id' => 3]];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                // Bawah (Peserta 13-24) - bracket 12 versi 1
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => "Peserta 15", 'id' => 15]];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => "Peserta 18", 'id' => 18]];
                $structure[] = ['player1' => ['name' => "Peserta 19", 'id' => 19], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 20", 'id' => 20], 'player2' => ['name' => "Peserta 21", 'id' => 21]];
                $structure[] = ['player1' => ['name' => "Peserta 22", 'id' => 22], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 23", 'id' => 23], 'player2' => ['name' => "Peserta 24", 'id' => 24]];
                break;
            case 25:
                // Atas (Peserta 1-12)
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => "Peserta 3", 'id' => 3]];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => "Peserta 12", 'id' => 12]];
                // Bawah (Peserta 13-25)
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => "Peserta 15", 'id' => 15]];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => "Peserta 18", 'id' => 18]];
                $structure[] = ['player1' => ['name' => "Peserta 19", 'id' => 19], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 20", 'id' => 20], 'player2' => ['name' => "Peserta 21", 'id' => 21]];
                $structure[] = ['player1' => ['name' => "Peserta 22", 'id' => 22], 'player2' => ['name' => "Peserta 23", 'id' => 23]];
                $structure[] = ['player1' => ['name' => "Peserta 24", 'id' => 24], 'player2' => ['name' => "Peserta 25", 'id' => 25]];
                break;
            case 26:
                // Atas (Peserta 1-13)
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => "Peserta 3", 'id' => 3]];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => "Peserta 13", 'id' => 13]];
                // Bawah (Peserta 14-26)
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => "Peserta 16", 'id' => 16]];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => "Peserta 19", 'id' => 19]];
                $structure[] = ['player1' => ['name' => "Peserta 20", 'id' => 20], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 21", 'id' => 21], 'player2' => ['name' => "Peserta 22", 'id' => 22]];
                $structure[] = ['player1' => ['name' => "Peserta 23", 'id' => 23], 'player2' => ['name' => "Peserta 24", 'id' => 24]];
                $structure[] = ['player1' => ['name' => "Peserta 25", 'id' => 25], 'player2' => ['name' => "Peserta 26", 'id' => 26]];
                break;
            case 27:
                // Atas (Peserta 1-13)
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => "Peserta 3", 'id' => 3]];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 5", 'id' => 5], 'player2' => ['name' => "Peserta 6", 'id' => 6]];
                $structure[] = ['player1' => ['name' => "Peserta 7", 'id' => 7], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => "Peserta 13", 'id' => 13]];
                // Bawah (Peserta 14-27)
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => "Peserta 16", 'id' => 16]];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => "Peserta 18", 'id' => 18]];
                $structure[] = ['player1' => ['name' => "Peserta 19", 'id' => 19], 'player2' => ['name' => "Peserta 20", 'id' => 20]];
                $structure[] = ['player1' => ['name' => "Peserta 21", 'id' => 21], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 22", 'id' => 22], 'player2' => ['name' => "Peserta 23", 'id' => 23]];
                $structure[] = ['player1' => ['name' => "Peserta 24", 'id' => 24], 'player2' => ['name' => "Peserta 25", 'id' => 25]];
                $structure[] = ['player1' => ['name' => "Peserta 26", 'id' => 26], 'player2' => ['name' => "Peserta 27", 'id' => 27]];
                break;
            case 28:
                // Atas (Peserta 1-14)
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => "Peserta 3", 'id' => 3]];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => "Peserta 7", 'id' => 7]];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => "Peserta 10", 'id' => 10]];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => "Peserta 12", 'id' => 12]];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => "Peserta 14", 'id' => 14]];
                // Bawah (Peserta 15-28)
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => "Peserta 17", 'id' => 17]];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => "Peserta 19", 'id' => 19]];
                $structure[] = ['player1' => ['name' => "Peserta 20", 'id' => 20], 'player2' => ['name' => "Peserta 21", 'id' => 21]];
                $structure[] = ['player1' => ['name' => "Peserta 22", 'id' => 22], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 23", 'id' => 23], 'player2' => ['name' => "Peserta 24", 'id' => 24]];
                $structure[] = ['player1' => ['name' => "Peserta 25", 'id' => 25], 'player2' => ['name' => "Peserta 26", 'id' => 26]];
                $structure[] = ['player1' => ['name' => "Peserta 27", 'id' => 27], 'player2' => ['name' => "Peserta 28", 'id' => 28]];
                break;
            case 29:
                // Atas (Peserta 1-14)
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => "Peserta 3", 'id' => 3]];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => "Peserta 7", 'id' => 7]];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 9", 'id' => 9], 'player2' => ['name' => "Peserta 10", 'id' => 10]];
                $structure[] = ['player1' => ['name' => "Peserta 11", 'id' => 11], 'player2' => ['name' => "Peserta 12", 'id' => 12]];
                $structure[] = ['player1' => ['name' => "Peserta 13", 'id' => 13], 'player2' => ['name' => "Peserta 14", 'id' => 14]];
                // Bawah (Peserta 15-29)
                $structure[] = ['player1' => ['name' => "Peserta 15", 'id' => 15], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => "Peserta 17", 'id' => 17]];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => "Peserta 19", 'id' => 19]];
                $structure[] = ['player1' => ['name' => "Peserta 20", 'id' => 20], 'player2' => ['name' => "Peserta 21", 'id' => 21]];
                $structure[] = ['player1' => ['name' => "Peserta 22", 'id' => 22], 'player2' => ['name' => "Peserta 23", 'id' => 23]];
                $structure[] = ['player1' => ['name' => "Peserta 24", 'id' => 24], 'player2' => ['name' => "Peserta 25", 'id' => 25]];
                $structure[] = ['player1' => ['name' => "Peserta 26", 'id' => 26], 'player2' => ['name' => "Peserta 27", 'id' => 27]];
                $structure[] = ['player1' => ['name' => "Peserta 28", 'id' => 28], 'player2' => ['name' => "Peserta 29", 'id' => 29]];
                break;
            case 30:
                // Atas (Peserta 1-15)
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => "Peserta 3", 'id' => 3]];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => "Peserta 7", 'id' => 7]];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => "Peserta 13", 'id' => 13]];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => "Peserta 15", 'id' => 15]];
                // Bawah (Peserta 16-30)
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 17", 'id' => 17], 'player2' => ['name' => "Peserta 18", 'id' => 18]];
                $structure[] = ['player1' => ['name' => "Peserta 19", 'id' => 19], 'player2' => ['name' => "Peserta 20", 'id' => 20]];
                $structure[] = ['player1' => ['name' => "Peserta 21", 'id' => 21], 'player2' => ['name' => "Peserta 22", 'id' => 22]];
                $structure[] = ['player1' => ['name' => "Peserta 23", 'id' => 23], 'player2' => ['name' => "Peserta 24", 'id' => 24]];
                $structure[] = ['player1' => ['name' => "Peserta 25", 'id' => 25], 'player2' => ['name' => "Peserta 26", 'id' => 26]];
                $structure[] = ['player1' => ['name' => "Peserta 27", 'id' => 27], 'player2' => ['name' => "Peserta 28", 'id' => 28]];
                $structure[] = ['player1' => ['name' => "Peserta 29", 'id' => 29], 'player2' => ['name' => "Peserta 30", 'id' => 30]];
                break;
            case 31:
                // Atas (Peserta 1-15)
                $structure[] = ['player1' => ['name' => "Peserta 1", 'id' => 1], 'player2' => ['name' => 'BYE', 'id' => 'bye']];
                $structure[] = ['player1' => ['name' => "Peserta 2", 'id' => 2], 'player2' => ['name' => "Peserta 3", 'id' => 3]];
                $structure[] = ['player1' => ['name' => "Peserta 4", 'id' => 4], 'player2' => ['name' => "Peserta 5", 'id' => 5]];
                $structure[] = ['player1' => ['name' => "Peserta 6", 'id' => 6], 'player2' => ['name' => "Peserta 7", 'id' => 7]];
                $structure[] = ['player1' => ['name' => "Peserta 8", 'id' => 8], 'player2' => ['name' => "Peserta 9", 'id' => 9]];
                $structure[] = ['player1' => ['name' => "Peserta 10", 'id' => 10], 'player2' => ['name' => "Peserta 11", 'id' => 11]];
                $structure[] = ['player1' => ['name' => "Peserta 12", 'id' => 12], 'player2' => ['name' => "Peserta 13", 'id' => 13]];
                $structure[] = ['player1' => ['name' => "Peserta 14", 'id' => 14], 'player2' => ['name' => "Peserta 15", 'id' => 15]];
                // Bawah (Peserta 16-31)
                $structure[] = ['player1' => ['name' => "Peserta 16", 'id' => 16], 'player2' => ['name' => "Peserta 17", 'id' => 17]];
                $structure[] = ['player1' => ['name' => "Peserta 18", 'id' => 18], 'player2' => ['name' => "Peserta 19", 'id' => 19]];
                $structure[] = ['player1' => ['name' => "Peserta 20", 'id' => 20], 'player2' => ['name' => "Peserta 21", 'id' => 21]];
                $structure[] = ['player1' => ['name' => "Peserta 22", 'id' => 22], 'player2' => ['name' => "Peserta 23", 'id' => 23]];
                $structure[] = ['player1' => ['name' => "Peserta 24", 'id' => 24], 'player2' => ['name' => "Peserta 25", 'id' => 25]];
                $structure[] = ['player1' => ['name' => "Peserta 26", 'id' => 26], 'player2' => ['name' => "Peserta 27", 'id' => 27]];
                $structure[] = ['player1' => ['name' => "Peserta 28", 'id' => 28], 'player2' => ['name' => "Peserta 29", 'id' => 29]];
                $structure[] = ['player1' => ['name' => "Peserta 30", 'id' => 30], 'player2' => ['name' => "Peserta 31", 'id' => 31]];
                break;
        }
        return $structure;
    }
    private function initializeFirstRound() {
        $firstRound = [];
        // Hanya logic versi 1
        if (in_array($this->participants, [3, 5, 6, 7, 9, 10, 11, 12, 13, 14, 15, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31])) {
            file_put_contents(__DIR__.'/debug-init.txt', 'Peserta: '.$this->participants."\n", FILE_APPEND);
            return $this->getSpecialCaseStructure($this->participants);
        }
        $nextPowerOf2 = pow(2, ceil(log($this->participants, 2)));
        $byes = $nextPowerOf2 - $this->participants;
        $participantIndex = 1;
        for ($i = 0; $i < $nextPowerOf2 / 2; $i++) {
            $match = [];
            if ($i < $byes / 2) {
                $match['player1'] = ['name' => "Peserta " . $participantIndex, 'id' => $participantIndex];
                $match['player2'] = ['name' => 'BYE', 'id' => 'bye'];
                $participantIndex++;
            } else {
                $match['player1'] = ['name' => "Peserta " . $participantIndex, 'id' => $participantIndex];
                $participantIndex++;
                if ($participantIndex <= $this->participants) {
                    $match['player2'] = ['name' => "Peserta " . $participantIndex, 'id' => $participantIndex];
                    $participantIndex++;
                } else {
                    $match['player2'] = ['name' => 'BYE', 'id' => 'bye'];
                }
            }
            $firstRound[] = $match;
        }
        return $firstRound;
    }
    private function generateRound($previousRound) {
        if ($previousRound === null) return [];
        if ($this->participants == 17 && isset($this->babak2_17v1)) {
            $newRound = [];
            $winner_b1 = $this->determineWinner($previousRound[0]);
            for ($i = 1; $i <= 14; $i += 2) {
                $newRound[] = [
                    'player1' => ['name' => $this->getPesertaName($i), 'id' => $i],
                    'player2' => ['name' => $this->getPesertaName($i+1), 'id' => $i+1],
                ];
            }
            $newRound[] = [
                'player1' => ['name' => $this->getPesertaName(15), 'id' => 15],
                'player2' => $winner_b1
            ];
            unset($this->babak2_17v1);
            return $newRound;
        }
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
        if ($this->participants == 17) {
            $labels = ['Play-in', '16 Besar', 'Per 8', 'Semifinal', 'Final'];
            $idx = 0;
            if (isset($bracket[1])) {
                $structure[$labels[$idx++]] = $bracket[1];
            }
            if (isset($bracket[2])) {
                $structure[$labels[$idx++]] = $bracket[2];
            }
            for ($i = 3; $i <= count($bracket); $i++) {
                $label = isset($labels[$idx]) ? $labels[$idx] : "Babak $i";
                $structure[$label] = $bracket[$i];
                $idx++;
            }
            return $structure;
        }
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
