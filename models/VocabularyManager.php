<?php

class VocabularyManager
{
    private \mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }
   
    public function translation_to($term) {
        $escapedInput = escapeshellarg($term);
        $pythonScript = __DIR__ . "/ai_translation.py";
        $command = "python " . escapeshellarg($pythonScript) . " $escapedInput 2>&1";
    
        $output = shell_exec($command);
        $translation = json_decode($output, true);
    
        if ($translation === null) {
            error_log("❌ Translation failed. Raw output: $output");
        }
    
        return $translation;
    }

    public function addWordToProfile($userId, $word, $translation)
    {
        $stmt = $this->db->prepare("INSERT INTO user_vocabulary 
            (user_id, term, translation, date_added,  points, next_review_date, review_days)
            VALUES (?, ?, ?, NOW(),  0, NOW(),0)");

        $stmt->bind_param("iss", $userId, $word, $translation);
        $stmt->execute();
        $stmt->close();
    }

    public function getWordsAddedToday($userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM user_vocabulary WHERE user_id = ? AND date_added = CURDATE()");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count;
    }

    public function getWordsToLearn($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM user_vocabulary WHERE user_id = ? AND to_learn = 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function countWordsToLearn($userId)
    {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM user_vocabulary WHERE user_id = ? AND to_learn = 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count;
    }

    public function setWordLearned($wordId)
    {
        $stmt = $this->db->prepare("UPDATE user_vocabulary SET  to_learn = 0,review_days = review_days + 1, next_review_date = CURDATE() + INTERVAL review_days DAY WHERE id = ?");
        $stmt->bind_param("i", $wordId);
        $stmt->execute();
        $stmt->close();
    }

    public function reducePoints($wordId)
    {
        $stmt = $this->db->prepare("UPDATE user_vocabulary SET points = points - 10 WHERE id = ?");
        $stmt->bind_param("i", $wordId);
        $stmt->execute();
        $stmt->close();
    }

    public function getWordsToRevise($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM user_vocabulary WHERE user_id = ? AND next_review_date <= CURDATE()");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function countWordsToRepeat($userId)
    {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM user_vocabulary WHERE user_id = ? AND to_learn = 0");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count;
    }

    public function updateRevision($wordId, $result)
    {
        if ($result === "Yes") {
            $stmt = $this->db->prepare("UPDATE user_vocabulary SET points = points + 10, review_days = review_days + 1, next_review_date = CURDATE() + INTERVAL review_days DAY WHERE id = ?");
        } else {
            $stmt = $this->db->prepare("UPDATE user_vocabulary SET points = points - 15, review_days = 0 WHERE id = ?");
        }
        $stmt->bind_param("i", $wordId);
        $stmt->execute();
        $stmt->close();
    }

    public function deleteWord($wordId)
    {
        $stmt = $this->db->prepare("DELETE FROM user_vocabulary WHERE id = ?");
        $stmt->bind_param("i", $wordId);
        $stmt->execute();
        $stmt->close();
    }

    public function getAllWords($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM user_vocabulary WHERE user_id = ? ORDER BY date_added DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getLeastPointWords($userId)
    {
        $stmt = $this->db->prepare("SELECT term, translation FROM user_vocabulary WHERE user_id = ? AND last_used != 0 ORDER BY points ASC LIMIT 20");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
