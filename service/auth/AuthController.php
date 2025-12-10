<?php
require_once __DIR__ . '/../db.php';

class AuthController 
{
    public static function loginOrRegister($provider, $id, $name, $email, $profile_pic) 
    {
        global $conn;

        // Cek apakah user sudah ada berdasarkan provider ID
        $column = $provider . "_id"; // google_id / facebook_id
        $stmt = $conn->prepare("SELECT id FROM users WHERE $column=? OR email=? LIMIT 1");
        $stmt->bind_param("ss", $id, $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if($user){
            $uid = $user['id']; // login langsung
        } else {
            // ambil username dari nama depan
            $username = explode(" ", $name)[0];
            $provider_label = ucfirst($provider);

            $stmt = $conn->prepare("
                INSERT INTO users (username,email,{$column},profile_pic,provider) 
                VALUES (?,?,?,?,?)
            ");
            $stmt->bind_param("sssss",$username,$email,$id,$profile_pic,$provider_label);
            $stmt->execute();
            $uid = $stmt->insert_id;
        }

        $_SESSION['user_id'] = $uid;
        return true;
    }
}
