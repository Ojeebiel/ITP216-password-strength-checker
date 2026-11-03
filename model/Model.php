<?php

require_once __DIR__ . '/../config/Database.php';

/**
 * Model: the ONLY place where SQL queries live.
 * Every method returns plain data (array / int / bool) and never touches
 * sessions, headers, or HTML.
 */
class Model {

    private PDO $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /* ---------------------------------------------------------------
     |  USERS
     * ------------------------------------------------------------- */

    /**
     * Finds a user by email.
     * @return array|false  [id, name, password_hash] or false
     */
    public function getUserByEmail(string $email) {
        try {
            $sql  = "SELECT id, name, password_hash FROM users WHERE email = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$email]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Model::getUserByEmail Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifies email + password.
     * @return array|false  [id, name] on success, false on failure
     */
    public function authenticate(string $email, string $password) {
        $user = $this->getUserByEmail($email);

        if ($user && password_verify($password, $user['password_hash'])) {
            return ['id' => (int) $user['id'], 'name' => $user['name']];
        }

        return false;
    }

    /**
     * Inserts a new user.
     * @return int|false  the new user's id, or false (e.g. duplicate email)
     */
    public function createUser(string $name, string $email, string $passwordHash) {
        try {
            $sql  = "INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$name, $email, $passwordHash]);

            return (int) $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Model::createUser Error: " . $e->getMessage());
            return false;
        }
    }

    /* ---------------------------------------------------------------
     |  LOGIN OTP
     * ------------------------------------------------------------- */

    /**
     * Replaces any existing OTP for the user with a new (hashed) one.
     */
    public function saveOtp(int $userId, string $otpHash, string $expiresAt): bool {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("DELETE FROM login_otp WHERE user_id = ?");
            $stmt->execute([$userId]);

            $stmt = $this->conn->prepare(
                "INSERT INTO login_otp (user_id, otp_code, expires_at) VALUES (?, ?, ?)"
            );
            $stmt->execute([$userId, $otpHash, $expiresAt]);

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Model::saveOtp Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches the stored OTP record for a user.
     * @return array|false  [otp_code, expires_at] or false
     */
    public function getOtp(int $userId) {
        try {
            $sql  = "SELECT otp_code, expires_at FROM login_otp WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Model::getOtp Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes the OTP record(s) for a user.
     */
    public function deleteOtp(int $userId): bool {
        try {
            $stmt = $this->conn->prepare("DELETE FROM login_otp WHERE user_id = ?");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Model::deleteOtp Error: " . $e->getMessage());
            return false;
        }
    }
}
