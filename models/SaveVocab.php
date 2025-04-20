<?php

class UserVocabulary
{
    private \mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Sprema selektovane reči korisnika u bazu.
     *
     * @param int $userId
     * @param array $vocabList - cela lista reči iz sesije (index => [term, translation])
     * @param array $selectedIndexes - niz indeksa koje je korisnik odabrao
     * @return void
     */
    public function saveSelectedWords(int $userId, array $vocabList, array $selectedIndexes): void
    {
        $stmt = $this->db->prepare("INSERT INTO user_vocabulary 
            (user_id, term, translation, date_added, last_used, points, next_review_date, review_days)
            VALUES (?, ?, ?, NOW(), NULL, 0, NOW(),0)
        ");

        if (!$stmt) {
            throw new Exception("Database error: " . $this->db->error);
        }

        foreach ($selectedIndexes as $index) {
            if (!isset($vocabList[$index])) continue;

            $term = $vocabList[$index][0];
            $translation = $vocabList[$index][1];

            $stmt->bind_param("iss", $userId, $term, $translation);
            $stmt->execute();
        }

        $stmt->close();
    }

    public function hasAnyWords(int $userId): bool
{
    $query = "SELECT 1 FROM user_vocabulary WHERE user_id = ? LIMIT 1";
    $stmt = $this->db->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();

    return $stmt->num_rows > 0;
}


}
