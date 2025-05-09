<?php

class UserVocabulary
{
    private \mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Sprema više riječi na temelju indeksa iz liste.
     */
    public function saveSelectedWords(int $userId, array $vocabList, array $selectedIndexes): void
    {
        $stmt = $this->db->prepare("INSERT INTO user_vocabulary 
            (user_id, term, translation, date_added, points, next_review_date)
            VALUES (?, ?, ?, NOW(), 0, NOW())"
        );

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

    /**
     * Sprema jednu riječ direktno.
     */
    public function saveSelectedWord(int $userId, string $term, string $translation): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO user_vocabulary 
                (user_id, term, translation, date_added, points, next_review_date)
            VALUES (?, ?, ?, NOW(), 0, NOW())"
        );

        if (!$stmt) {
            throw new Exception("Greška pri pripremi upita: " . $this->db->error);
        }

        $stmt->bind_param("iss", $userId, $term, $translation);

        if (!$stmt->execute()) {
            throw new Exception("Greška pri izvršavanju upita: " . $stmt->error);
        }

        $stmt->close();
    }

    /**
     * Provjerava ima li korisnik već spremljene riječi.
     */
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
