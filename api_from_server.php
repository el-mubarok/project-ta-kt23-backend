<?php 
declare(strict_types=1);
error_reporting(E_ALL ^ E_WARNING);
require 'vendor/autoload.php';

use OneSignal\Config;
use OneSignal\OneSignal;
use Nyholm\Psr7\Factory\Psr17Factory;
use GuzzleHttp\Client;

require __DIR__ . '/vendor/autoload.php';
require_once 'db.php';
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json');

define("NOTIFICATION_NORMAL", "notification");
define("NOTIFICATION_SILENT", "silent_notification");
define("NOTIFICATION_GET_DEVICES", "devices");

function sendNotification(
  $type, $playerId = null, $additionalData = null
) {
  // 
  $config = new Config(
    '6f54bdf3-5bd8-4d55-86f8-32f19d342abe', 
    'ZjMxNmMwNzAtMDhkZi00MWJlLTgwY2ItNzZiY2M5NDE2Mzk0'
  );
  $httpClient = new Client();
  $requestFactory = $streamFactory = new Psr17Factory();

  $oneSignal = new OneSignal($config, $httpClient, $requestFactory, $streamFactory);

  switch($type) {
    case NOTIFICATION_GET_DEVICES:
      $devices = $oneSignal->devices()->getAll();
      return json_encode($devices, JSON_PRETTY_PRINT);
      break;
    case NOTIFICATION_SILENT:
      $result = $oneSignal->notifications()->add([
        'headings' => [
          'en' => 'Incoming Attendance'
        ],
        'contents' => [
          'en' => '...'
        ],
        'include_player_ids' => [$playerId],
        // 'content_available' => 1,
        'data' => $additionalData,
      ]);
      return $result;
      break;
    default:
      return ["status" => "failed"];
  }
}

function generateAttendanceCode() {
  $date = date("Y-m-d H:i:s");
  $encodedDate = encryptCode($date);

  return json_encode([
    "code" => 200,
    "data" => $encodedDate
  ], JSON_PRETTY_PRINT);
}

function encryptCode($code) {
  $simple_string = "$code::".password_hash("$code", PASSWORD_BCRYPT);    
  // Store the cipher method
  $ciphering = "AES-128-CTR";
  // Use OpenSSl Encryption method
  $iv_length = openssl_cipher_iv_length($ciphering);
  $options = 0;
  // Non-NULL Initialization Vector for encryption
  $encryption_iv = '1234567891011121';
  // Store the encryption key 
  $encryption_key = "attendence_app";
  // Use openssl_encrypt() function to encrypt the data
  $encryption = openssl_encrypt(
    $simple_string, 
    $ciphering,
    $encryption_key, 
    $options, 
    $encryption_iv
  );

  $result = "";

  // 2 iteration base64 encode
  for ($i=0; $i<1; $i++) { 
    $result .= base64_encode($encryption);
  }

  return $result;
}

/**
 * TO DO
 * 
 * try catch to handle error when decoding
 * and decrypting
 * 
 * this usefull for no one can't re create
 * qr code
 */
function decryptCode($code) {
  // Store the cipher method
  $ciphering = "AES-128-CTR";
  // Use OpenSSl Encryption method
  $iv_length = openssl_cipher_iv_length($ciphering);
  $options = 0;
  // Non-NULL Initialization Vector for decryption
  $decryption_iv = '1234567891011121';
  // Store the decryption key
  $decryption_key = "attendence_app";

  $result = "";

  // 2 iteration base64 decode
  for ($i=0; $i<1; $i++) { 
    $result .= base64_decode($code);
  }

  // Use openssl_decrypt() function to decrypt the data
  $decryption = openssl_decrypt (
    $result, 
    $ciphering, 
    $decryption_key, 
    $options, 
    $decryption_iv
  );

  return explode("::", $decryption)[0];
  // return $decryption;
}

function responseError($code = 400, $codeResponse = 400, $message = null){
  $_message = $message == null ? "failed to handle your request :(" : $message;
  // always return error code 400
  http_response_code($codeResponse);
  return json_encode([
    "code" => $code,
    "message" => $_message
  ], JSON_PRETTY_PRINT);
}

function updateAttempt($deviceId) {
  $check = DB::run(
    "SELECT * FROM login_attempt WHERE device_id=?",
    [$deviceId]
  )->fetch();

  if($check){
    $now = strtotime(date("Y-m-d H:i:s"));
    $lastAttempt = strtotime($check['update_at']);
    $minutesDiff = round(($now - $lastAttempt) / 60,2);
    $attempt = $check['attempt'];

    // echo "last attempt time: $minutesDiff";
    if($attempt >= 3 && $minutesDiff <= 5){
      // return dont continue
      return $attempt;
    }else if($attempt >= 3 && $minutesDiff >= 5){
      // reset
      $stmt = DB::run(
        "UPDATE login_attempt SET attempt=1 WHERE device_id=?", 
        [$deviceId]
      );
    }else{
      // do update attempt count
      $stmt = DB::run(
        "UPDATE login_attempt SET attempt=attempt+1 WHERE device_id=?", 
        [$deviceId]
      );
    }
  }else{
    // insert when not found
    $stmt = DB::prepare("INSERT INTO login_attempt VALUES (NULL, ?, 1, NULL)");
    $stmt->execute([$deviceId]);
  }

  // check attempt count and return
  $check = DB::run(
    "SELECT * FROM login_attempt WHERE device_id=?",
    [$deviceId]
  )->fetch();
  $attempt = $check['attempt'];

  return $attempt;
}

if(isset($_GET['generate'])){
  // echo generateAttendanceCode();
  // write to db
  if(isset($_POST["date"]) && isset($_POST["admin_id"])){
    $date = $_POST["date"];
    $dateOnly = date("Y-m-d", strtotime($date))."%";
    // $dateOnly = "2022-03-03%";
    $date = date("Y-m-d H:i:s", strtotime($date));
    $dateEnd = strtotime("$date + 30 minute");
    $dateEnd = date("Y-m-d H:i:s", $dateEnd);
    $admin = $_POST["admin_id"];
    $encodedDate = encryptCode($date);

    // check is session date exists
    // $existedSession = (object) DB::run(
    //   // "SELECT * FROM attendance_session WHERE session_date=?",
    //   "SELECT * FROM attendance_session WHERE session_date LIKE ?" 
    //   [ $dateOnly ]
    // )->fetch();

    $existedSession = DB::prepare(
      "SELECT * FROM attendance_session WHERE session_date LIKE :date
      ORDER BY session_date DESC" 
    );
    $existedSession->bindParam(':date', $dateOnly);
    $existedSession->execute();
    $existedSession = $existedSession->fetch();

    // var_dump($existedSession);
    // return true;

    // session date doesn't exists
    // create one
    // if(isset($existedSession->scalar)){
    //   if(!$existedSession->scalar){
    //   }
    // }
    if(!$existedSession){
      $statement = DB::prepare(
        "INSERT INTO attendance_session VALUES (NULL, ?, ?, ?, 0, 0, NULL, NUll)"
      );
  
      if(!$statement->execute([
        $admin, $date, $dateEnd
      ])){
        echo json_encode([
          "code" => 401,
          "message" => "failed to create session"
        ], JSON_PRETTY_PRINT);
        return true;
      }

      // get latest inserted id
      $lastId = DB::lastInsertId();
      // insert user with role user into
      // session_detail table
      $result = DB::run(
        "INSERT INTO session_detail (attendance_session_id, user_id)
          SELECT ?, id FROM user WHERE role='user'", 
        [ $lastId ]
      );

      echo json_encode([
        "code" => 200,
        "data" => [
          "qr_code" => $encodedDate,
          "start" => $date,
          "end" => $dateEnd
        ],
        "message" => "session created"
      ], JSON_PRETTY_PRINT);
      return true;
    }
    
    $encodedDate = encryptCode($existedSession['session_date']);

    echo json_encode([
      "code" => 200,
      "data" => [
        "qr_code" => $encodedDate,
        "start" => $existedSession["session_date"],
        "end" => $existedSession["session_date_end"]
      ],
      "message" => "session created"
    ], JSON_PRETTY_PRINT);
  }else{
    echo responseError();
  }
}else if(isset($_GET['users'])){
  $statement = DB::run("SELECT * FROM user");
  $users = [];

  while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    array_push($users, $row);
  }

  echo json_encode([
    "code" => 200,
    "data" => $users
  ], JSON_PRETTY_PRINT);
}else if(isset($_GET['listed_devices'])){
  echo sendNotification(NOTIFICATION_GET_DEVICES);
}else if(isset($_GET['send_notif'])){
  if(isset($_POST['device'])){
    $data = $_POST;
    echo json_encode($data, JSON_PRETTY_PRINT);
  }else{
    echo json_encode([
      "code" => 401,
      "message" => "no data"
    ], JSON_PRETTY_PRINT);
  }
}else if(isset($_GET['password'])){
  // var_dump(count($_POST));
  if(isset($_POST['password'])){
    $password = $_POST['password'];
    echo json_encode([
      "code" => 200,
      "data" => [
        "plain" => $password,
        "encrypted" => password_hash(
          $password,
          PASSWORD_BCRYPT
        )
      ]
    ], JSON_PRETTY_PRINT);
  }else{
    echo json_encode([
      "code" => 401,
      "message" => "no data"
    ], JSON_PRETTY_PRINT);
  }
}else if(isset($_GET['scan'])){
  if(isset($_POST["data"]) && isset($_POST["user_id"])){
    $date = decryptCode($_POST["data"]);
    $userId = $_POST['user_id'];
    
    // print_r($date);

    // if decoded $date is same as today date
    if(date("Y-m-d", strtotime($date)) == date("Y-m-d")){
      $currentSession = (object) DB::run(
        "SELECT * FROM attendance_session WHERE session_date=?", [ $date ]
      )->fetch();
      
    //   print_r($currentSession);
  
      // is date exists on attendance_session
      if($currentSession){
        // get admin info
        $adminData = (object) DB::run(
          "SELECT * FROM user WHERE id=?", 
          [ $currentSession->admin_id ]
        )->fetch();
        
        // print_r($adminData);

        $sessionId = $currentSession->id;
        $onTimePresent = $currentSession->present_on_time;
        $latePresent = $currentSession->present_late;
        $isLate = false;
  
        // check is user late present or not
        if($currentSession->session_date_end){
          $now = date("Y-m-d H:i:s");
          $endDate = date(
            "Y-m-d H:i:s",
            strtotime(
              $currentSession->session_date_end
            )
          );
  
          if($now <= $endDate){
            // on time present
            $onTimePresent += 1;
            $isLate = false;
          }else{
            // late present
            $latePresent += 1;
            $isLate = true;
          }
        }else{
          $onTimePresent += 1;
          $isLate = false;
        }

        // check is current user available in session_detail
        $isUserAvail = DB::run(
          "SELECT * FROM session_detail WHERE user_id=?", [ $userId ]
        )->fetch();
        
        // print_r($isUserAvail);

        if($isUserAvail){
          // check is on_time and late 0 or 1
          if($isUserAvail['present_on_time'] == 0 && 
            $isUserAvail['present_late'] == 0){
            // do nothing
          }else {
            echo responseError(401, 200);
            return true;
          }
        }

        // insert session_detail
        // $sessionDetailStatement = DB::prepare(
        //   "INSERT INTO session_detail VALUES (NULL, ?, ?, ?, ?, NULL)"
        // );
        $sessionDetailStatement = DB::prepare(
          "UPDATE session_detail SET present_on_time=?, present_late=?
          WHERE attendance_session_id=? AND user_id=?"
        );
        $sessionDetailStatementResult = $sessionDetailStatement->execute([
          $isLate ? 0 : 1,
          $isLate ? 1 : 0,
          $sessionId, 
          $userId,
        ]);
  
        // update attendance_session counter
        $updatedAttendanceSession = DB::run(
          "UPDATE attendance_session 
          SET present_on_time=?, present_late=?
          WHERE id=?",
          [$onTimePresent, $latePresent, $sessionId]
        );

        // regenerate qr code based on $date
        $encodedDate = encryptCode($date);

        // send notification to admin that pair with
        // $currentSession
        $notificationResult = sendNotification(
          NOTIFICATION_SILENT,
          $adminData->messaging_id,
          [
            "qr_code" => $encodedDate,
            "present_on_time" => $currentSession->present_on_time,
            "present_late" => $currentSession->present_late,
            "created" => date("Y-m-d H:i:s")
          ]
        );
  
        echo json_encode([
          "code" => 200,
          "data" => [
            "notification" => $notificationResult,
            "additional_data" => [
              "qr_code" => $encodedDate,
              "present_on_time" => $currentSession->present_on_time,
              "present_late" => $currentSession->present_late,
              "created" => date("Y-m-d H:i:s")
            ]
          ],
          "message" => "scan done"
        ], JSON_PRETTY_PRINT);
      }
    }
    // denied request, dont continue
    // return error response
    else{
      echo responseError();
    }
  }else{
    echo responseError();
  }
}else if(isset($_GET['login'])){
  if(count($_POST) > 0){
    $requiredField = [
      "username", "password", "device_id",
      "role"
    ];
    $data = [];
  
    foreach($requiredField as $req){
      $data[$req] = $_POST[$req];
    }

    $attempt = DB::run(
      "SELECT * FROM login_attempt WHERE device_id=?",
      [$data['device_id']]
    )->fetch();

    if($attempt){
      if($attempt["attempt"] >= 3){
        echo responseError(404, 401, "please retry within 5 minutes");
        return true;
      }
    }

    // check is device id registered
    $checkDeviceId = DB::run(
      "SELECT * FROM user
      WHERE username=?
      AND device_id=?
      AND role=?",
      [
        $data['username'],
        $data['device_id'],
        $data['role']
      ]
    )->fetch();
    if($checkDeviceId['device_id'] == null){
      // register device id
      $updatedDeviceId = DB::run(
        "UPDATE user 
        SET device_id=?
        WHERE username=?",
        [$data['device_id'], $data['username']]
      );
    }

    $selectedColumn = implode(',', [
      "id", "full_name", "role", "created_at",
      "updated_at"
    ]);
    $statement = DB::run(
      "SELECT * FROM user
      WHERE username=?
      AND device_id=?
      AND role=?",
      [
        $data['username'],
        $data['device_id'],
        $data['role']
      ]
    );
    $result = $statement->fetch();

    if($result){
      $passwordCheck = password_verify(
        $data['password'],
        $result['password']
      );
      // var_dump($passwordCheck);

      if(!$passwordCheck){
        // update attempt when error
        updateAttempt($data['device_id']);
        echo responseError(402, 401, "wrong username/password");
        return true;
      }

      unset($result["password"]);
      unset($result["messaging_id"]);
      unset($result["device_id"]);

      // remove attempt
      $deleteDeviceIdAttempt = DB::run(
        "DELETE FROM login_attempt WHERE device_id=?", 
        [$data['device_id']]
      );

      echo json_encode([
        "code" => 200,
        "data" => $result,
        "message" => "logged in"
      ], JSON_PRETTY_PRINT);
    }else{
      // update attempt when error
      updateAttempt($data['device_id']);
      echo responseError(403, 400, "wrong username/password");
    }
  }
}else if(isset($_GET['attendance_info'])){
  if(isset($_GET['user_id'])){
    $userId = $_GET['user_id'];

    $data = DB::run(
      'SELECT * FROM user_attendance_by_user_id WHERE user_id=?', 
      [$userId]
    )->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
      "code" => 200,
      "data" => $data,
      "message" => "user attendance info (all)"
    ], JSON_PRETTY_PRINT);
  }else{
    echo responseError();
  }
}else if(isset($_GET['user_detail'])){
  if(isset($_GET['user_id'])){
    $userId = $_GET['user_id'];

    $statement = DB::run(
      "SELECT * FROM user WHERE id=?",
      [$userId]
    );
    $data = $statement->fetch();

    unset($data["password"]);
    unset($data["messaging_id"]);
    unset($data["device_id"]);

    echo json_encode([
      "code" => 200,
      "data" => $data,
      "message" => "data user"
    ], JSON_PRETTY_PRINT);
  }else{
    echo responseError();
  }
}else if(isset($_GET['reset_password'])){
  if(isset($_POST['user_id']) && 
    isset($_POST['password']) && 
    isset($_POST['device_id'])){
    $password = password_hash(
      $_POST['password'], 
      PASSWORD_BCRYPT
    );
    $userId = $_POST['user_id'];
    $deviceId = $_POST['device_id'];

    $result = DB::run(
      "UPDATE user SET password=? WHERE id=? AND device_id=?", 
      [$password, $userId, $deviceId]
    )->rowCount();

    if($result == 1){
      echo json_encode([
        "code" => 200,
        "message" => "password updated"
      ], JSON_PRETTY_PRINT);
    }else{
      echo responseError();
    }
  }else{
    echo responseError();
  }
}