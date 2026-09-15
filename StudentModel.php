<?php
/**
 * Student Model
 */

class Student {
    private $conn;
    private $table = 'students';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (user_id, admission_no, class_id, parent_phone, parent_email, dob, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('isisss',
            $data['user_id'],
            $data['admission_no'],
            $data['class_id'],
            $data['parent_phone'],
            $data['parent_email'],
            $data['dob']
        );

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Student created successfully', 'id' => $this->conn->insert_id];
        }
        return ['success' => false, 'message' => 'Error creating student'];
    }

    public function getById($id) {
        $query = "SELECT s.*, u.name, u.email, c.name as class_name 
                  FROM {$this->table} s 
                  JOIN users u ON s.user_id = u.id 
                  JOIN classes c ON s.class_id = c.id 
                  WHERE s.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getByUserId($user_id) {
        $query = "SELECT s.*, u.name, u.email, c.name as class_name 
                  FROM {$this->table} s 
                  JOIN users u ON s.user_id = u.id 
                  JOIN classes c ON s.class_id = c.id 
                  WHERE s.user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAll() {
        $query = "SELECT s.*, u.name, u.email, c.name as class_name 
                  FROM {$this->table} s 
                  JOIN users u ON s.user_id = u.id 
                  JOIN classes c ON s.class_id = c.id 
                  ORDER BY u.name ASC";
        
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getByClass($class_id) {
        $query = "SELECT s.*, u.name, u.email 
                  FROM {$this->table} s 
                  JOIN users u ON s.user_id = u.id 
                  WHERE s.class_id = ? 
                  ORDER BY u.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $class_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function update($id, $data) {
        $fields = [];
        $values = [];
        $types = '';

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
            $types .= 's';
        }

        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        $values[] = $id;
        $types .= 'i';

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Student updated successfully'];
        }
        return ['success' => false, 'message' => 'Error updating student'];
    }
}
?>
