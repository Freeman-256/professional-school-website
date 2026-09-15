<?php
/**
 * Grade Model
 */

class Grade {
    private $conn;
    private $table = 'grades';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (student_id, subject_id, term, score, grade, teacher_id, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iiidsi',
            $data['student_id'],
            $data['subject_id'],
            $data['term'],
            $data['score'],
            $data['grade'],
            $data['teacher_id']
        );

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Grade created successfully'];
        }
        return ['success' => false, 'message' => 'Error creating grade'];
    }

    public function getByStudent($student_id, $term = null) {
        $query = "SELECT g.*, s.name as subject_name, su.name as student_name 
                  FROM {$this->table} g 
                  JOIN subjects s ON g.subject_id = s.id 
                  JOIN students st ON g.student_id = st.id 
                  JOIN users su ON st.user_id = su.id 
                  WHERE g.student_id = ?";
        
        if ($term) {
            $query .= " AND g.term = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('ii', $student_id, $term);
        } else {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $student_id);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAverageByStudent($student_id) {
        $query = "SELECT AVG(score) as average FROM {$this->table} WHERE student_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $student_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function update($id, $data) {
        $fields = [];
        $values = [];
        $types = '';

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
            $types .= is_numeric($value) ? 'd' : 's';
        }

        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        $values[] = $id;
        $types .= 'i';

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Grade updated successfully'];
        }
        return ['success' => false, 'message' => 'Error updating grade'];
    }
}
?>
