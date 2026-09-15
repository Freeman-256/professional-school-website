<?php
/**
 * User Model
 */

class User {
    private $conn;
    private $table = 'users';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (name, email, password, role, phone, address, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Query error: ' . $this->conn->error];
        }

        $hash_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt->bind_param('ssssss',
            $data['name'],
            $data['email'],
            $hash_password,
            $data['role'],
            $data['phone'],
            $data['address']
        );

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User created successfully', 'id' => $this->conn->insert_id];
        }
        return ['success' => false, 'message' => 'Error creating user'];
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAll($role = null) {
        $query = "SELECT * FROM {$this->table}";
        if ($role) {
            $query .= " WHERE role = '" . $this->conn->real_escape_string($role) . "'";
        }
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
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
        if (!$stmt) {
            return ['success' => false, 'message' => 'Query error'];
        }

        $stmt->bind_param($types, ...$values);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User updated successfully'];
        }
        return ['success' => false, 'message' => 'Error updating user'];
    }

    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User deleted successfully'];
        }
        return ['success' => false, 'message' => 'Error deleting user'];
    }
}
?>
