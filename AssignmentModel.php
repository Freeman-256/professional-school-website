<?php
/**
 * Assignment Model
 */

class Assignment {
    private $conn;
    private $table = 'assignments';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (teacher_id, class_id, subject_id, title, description, due_date, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iissss',
            $data['teacher_id'],
            $data['class_id'],
            $data['subject_id'],
            $data['title'],
            $data['description'],
            $data['due_date']
        );

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Assignment created successfully', 'id' => $this->conn->insert_id];
        }
        return ['success' => false, 'message' => 'Error creating assignment'];
    }

    public function getByClass($class_id) {
        $query = "SELECT a.*, u.name as teacher_name, s.name as subject_name 
                  FROM {$this->table} a 
                  JOIN users u ON a.teacher_id = u.id 
                  JOIN subjects s ON a.subject_id = s.id 
                  WHERE a.class_id = ? 
                  ORDER BY a.due_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $class_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT a.*, u.name as teacher_name, s.name as subject_name 
                  FROM {$this->table} a 
                  JOIN users u ON a.teacher_id = u.id 
                  JOIN subjects s ON a.subject_id = s.id 
                  WHERE a.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
