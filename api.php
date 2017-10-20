<?php
/**
 * Created by PhpStorm.
 * User: Hollyphat
 * Date: 10/18/2017
 * Time: 12:54 AM
 */
header("Access-Control-Allow-Origin: *");
define('ENV','online');

if(ENV == "online") {
    define('DB_HOST', 'localhost');
    define('DB_TABLE', 'onlinem1_memo');
    define('DB_USER', 'onlinem1_memo');
    define('DB_PASSWORD', 'Nigeria1234');

}else {
    define('DB_HOST', 'localhost');
    define('DB_TABLE', 'free_mobile_memo');
    define('DB_USER', 'root');
    define('DB_PASSWORD', '');
}

try {
    $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_TABLE, DB_USER, DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
}
catch (PDOException $e){
    die('<br/><center><font size="15">Could not connect with database</font></center>');
}


function msg_count($user_id)
{
    //MESSAGE COUNTS
    global $db;

    $sql_msg = $db->prepare("SELECT NULL FROM messages WHERE sender = :me");
    $sql_msg->execute(array(
        'me' => $user_id
    ));

    $sent = $sql_msg->rowCount();

    $sql_msg->closeCursor();


    $sql_msg = $db->prepare("SELECT NULL FROM received WHERE receiver = :me");
    $sql_msg->execute(array(
        'me' => $user_id
    ));

    $inbox = $sql_msg->rowCount();

    $sql_msg->closeCursor();

    $sql_msg = $db->prepare("SELECT NULL FROM received WHERE receiver = :me and msg_status = :status");
    $sql_msg->execute(array(
        'me' => $user_id,
        'status' => 1
    ));

    $read = $sql_msg->rowCount();

    $sql_msg->closeCursor();


    $stats = array(
        'sent' => $sent,
        'inbox' => $inbox,
        'read' => $read
    );



    return $stats;
}

function user_details($id,$v){
    global $db;
    $sql = $db->prepare("SELECT * FROM users WHERE id = :id or username = :id");
    $sql->execute(array(
        'id' => $id
    ));

    $rs = $sql->fetch(PDO::FETCH_ASSOC);

    $sql->closeCursor();

    return $rs[$v];
}

if(isset($_POST['register'])){
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = $db->prepare("SELECT NULL FROM users WHERE username = :user");
    $sql->execute(array('user' => $username));
    $n = $sql->rowCount();

    if($n > 0){
        $out = array('ok' => 0, 'msg' => "Username already exist, try again later!");
        echo json_encode($out);

    }else{
        $in = $db->prepare("INSERT INTO users(username, password, name, date_reg) VALUES (:username, :password, :name, :date_reg)");
        $in->execute(array(
            'username' => $username,
            'password' => $password,
            'name' => $name,
            'date_reg' => time()
        ));

        $in->closeCursor();

        $out = array('ok' => 1, 'msg' => "Registration successful, you can now login");
        echo json_encode($out);
    }

    $sql->closeCursor();

    exit();
}

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = $db->prepare("SELECT * FROM users WHERE username = :user and password = :password");
    $sql->execute(array(
            'user' => $username,
            'password' => $password
        ));
    $n = $sql->rowCount();

    if($n == 0){
        $out = array('ok' => 0, 'msg' => "Invalid login details");
    }else{
        $rs = $sql->fetch(PDO::FETCH_ASSOC);
        $out = array();

        $record = array(
            'user_id' => $rs['id'],
            'username' => $rs['username'],
            'name' => $rs['name']
        );

        $out['ok'] = 1;
        $out['record'] = $record;

        //MESSAGE COUNTS

        $sql_msg = $db->prepare("SELECT NULL FROM messages WHERE sender = :me");
        $sql_msg->execute(array(
            'me' => $rs['id']
        ));

        $sent = $sql_msg->rowCount();

        $sql_msg->closeCursor();


        $sql_msg = $db->prepare("SELECT NULL FROM received WHERE receiver = :me");
        $sql_msg->execute(array(
            'me' => $rs['id']
        ));

        $inbox = $sql_msg->rowCount();

        $sql_msg->closeCursor();

        $sql_msg = $db->prepare("SELECT NULL FROM received WHERE receiver = :me and msg_status = :status");
        $sql_msg->execute(array(
            'me' => $rs['id'],
            'status' => 1
        ));

        $read = $sql_msg->rowCount();

        $sql_msg->closeCursor();

        $sql->closeCursor();

        $stats = array(
            'sent' => $sent,
            'inbox' => $inbox,
            'read' => $read
        );

        $out['stats'] = $stats;
    }
    echo json_encode($out);
    $sql->closeCursor();

    exit();
}


if(isset($_GET['user_list2'])){
    $user = $_GET['user'];

    $sql = $db->prepare("SELECT id,name FROM users WHERE id != :me");
    $sql->execute(array(
        'me' => $user
    ));


//    $list_1 = '<li>
//        <a href="#" class="item-link smart-select">
//                        <select name="receiver" id="rec" required="" data-open-in="picker">';
    $list_1 = '<li><a href="#" class="item-link smart-select">';
    $list_1 .= ' <select name="receiver">';

    while($rs = $sql->fetch(PDO::FETCH_ASSOC)){
        $list_1 .= "<option value='".$rs['id']."'>".$rs['name']."</option>";
    }

    $list_1 .= '</select><div class="item-content"><div class="item-inner"><div class="item-title">Receiver</div></div></div></a></li>';
    //echo $list_1;

    $sql->closeCursor();



    $sql = $db->prepare("SELECT id, name FROM users WHERE id != :me");
    $sql->execute(array(
        'me' => $user
    ));

    $list_1 .= '<li><a href="#" class="item-link smart-select">';
    $list_1 .= ' <select name="cc[]" multiple>';

    while($rs = $sql->fetch(PDO::FETCH_ASSOC)){
        $list_1 .= "<option value='".$rs['id']."'>".$rs['name']."</option>";
    }

    $list_1 .= '</select><div class="item-content"><div class="item-inner"><div class="item-title">CC</div></div></div></a></li>';
    echo $list_1;


    exit;
}


if(isset($_GET['user_list'])){
    $user = $_GET['user'];

    $sql = $db->prepare("SELECT id,name FROM users WHERE id != :me");
    $sql->execute(array(
        'me' => $user
    ));


    $list_1 =array();

    while($rs = $sql->fetch(PDO::FETCH_ASSOC)){
        $list_1[] = array('id' => $rs['id'], 'name' => $rs['name']);
    }



    $sql->closeCursor();



    $sql = $db->prepare("SELECT id, name FROM users WHERE id != :me");
    $sql->execute(array(
        'me' => $user
    ));

    $out = array('ok' => 1, 'result' => $list_1);

    echo json_encode($out);


    exit;
}

if(isset($_POST['send_msg'])){
    $user_id = $_POST['user_id'];
    $receiver= $_POST['receiver'];
    $title = $_POST['title'];
    $msg = $_POST['msg'];

    $in = $db->prepare("INSERT INTO messages(sender,title,date_sent,msg) VALUES(:sender, :title, :date_sent, :msg)");
    $in->execute(array(
        'sender' => $user_id,
        'title' => $title,
        'date_sent' => time(),
        'msg' => $msg
    ));

    $in_id = $db->lastInsertId();

    $in->closeCursor();

    //receiver

    $in2 = $db->prepare("INSERT INTO received(message_id,message_type,receiver,msg_status) VALUES(:m_id, :m_type, :receiver, :msg_status) ");

    $in2->execute(array(
        'm_id' => $in_id,
        'm_type' => 0,
        'receiver' => $receiver,
        'msg_status' => 0
    ));

    $in2->closeCursor();

    $cc = $_POST['cc'];

    $cc_array = explode(",",$cc);

    foreach ($cc_array as $ccv) {
        $in2 = $db->prepare("INSERT INTO received(message_id,message_type,receiver,msg_status) VALUES(:m_id, :m_type, :receiver, :msg_status) ");

        $in2->execute(array(
            'm_id' => $in_id,
            'm_type' => 1,
            'receiver' => $ccv,
            'msg_status' => 0
        ));

        $in2->closeCursor();
    }

    $msg_stats = msg_count($user_id);
    $out = array('msg' => "Message sent successfully", 'stats' => $msg_stats);

    echo json_encode($out);
    exit();

}


if(isset($_GET['msg_list'])){
    $user_id = $_GET['user'];

    $msg_stats = msg_count($user_id);
    $out = array('stats' => $msg_stats);

    echo json_encode($out);
    exit;
}


if(isset($_GET['load_read'])){
    $user_id = $_GET['user'];

    $sql = $db->prepare("SELECT id,message_id,msg_status FROM received WHERE receiver = :me and msg_status = :status ORDER BY id DESC");
    $sql->execute(array(
        'me' => $user_id,
        'status' => 1
    ));

    $count = $sql->rowCount();

    if($count == 0){
        $list = "<p class='center'>No message</p>";
    }else {

        $list = '<div class="list-block media-list"><ul>';
        while ($rs = $sql->fetch(PDO::FETCH_ASSOC)) {
            $sql_in = $db->prepare("SELECT title,sender,date_sent FROM messages WHERE id = :id");
            $sql_in->execute(array(
                'id' => $rs['message_id']
            ));
            $sql_in_rs = $sql_in->fetch(PDO::FETCH_ASSOC);

            $status = "";
            $list .= '<li>
                        <a href="view_inbox.html" data-id="' . $rs['id'] . '" class="item-link item-content view-msg">
                            <div class="item-inner">
                                <div class="item-title-row">
                                    <div class="item-title">' . user_details($sql_in_rs['sender'], "name") . '</div>
                                    <div class="item-after">' . $status . '</div>
                                </div>
                                <div class="item-subtitle">' . $sql_in_rs['title'] . '</div>
                                <div class="item-text">' . date("F d, y h:i a", $sql_in_rs['date_sent']) . '</div>
                            </div>
                        </a>
                    </li>';

        }

        $list .= "</ul></div>";
    }

    echo $list;
    exit();
}

if(isset($_GET['load_sent'])){
    $user_id = $_GET['user'];

    $sql = $db->prepare("SELECT id,title,sender,date_sent FROM messages WHERE sender = :me ORDER BY id DESC");
    $sql->execute(array(
        'me' => $user_id
    ));
    $count = $sql->rowCount();

    if($count == 0){
        $list = "<p class='center'>No memo</p>";
    }else {


        $list = '<div class="list-block media-list"><ul>';
        while ($rs = $sql->fetch(PDO::FETCH_ASSOC)) {
            $sql_in = $db->prepare("SELECT receiver FROM received WHERE message_id = :id ORDER BY id ASC");
            $sql_in->execute(array(
                'id' => $rs['id']
            ));
            $sql_in_rs = $sql_in->fetch(PDO::FETCH_ASSOC);

            $status = "";
            $list .= '<li>
                        <a href="view_sent.html" data-id="' . $rs['id'] . '" class="item-link item-content view-msg">
                            <div class="item-inner">
                                <div class="item-title-row">
                                    <div class="item-title">' . user_details($sql_in_rs['receiver'], "name") . '</div>
                                    <div class="item-after">' . $status . '</div>
                                </div>
                                <div class="item-subtitle">' . $rs['title'] . '</div>
                                <div class="item-text">' . date("F d, y h:i a", $rs['date_sent']) . '</div>
                            </div>
                        </a>
                    </li>';

        }

        $list .= "</ul></div>";
    }

    echo $list;
    exit();
}


if(isset($_GET['load_inbox'])){
    $user_id = $_GET['user'];

    $sql = $db->prepare("SELECT id,message_id,msg_status FROM received WHERE receiver = :me ORDER BY id DESC");
    $sql->execute(array(
        'me' => $user_id
    ));
    $count = $sql->rowCount();

    if($count == 0){
        $list = "<p class='center'>No memo</p>";
    }else {


        $list = '<div class="list-block media-list"><ul>';
        while ($rs = $sql->fetch(PDO::FETCH_ASSOC)) {
            $sql_in = $db->prepare("SELECT title,sender,date_sent FROM messages WHERE id = :id");
            $sql_in->execute(array(
                'id' => $rs['message_id']
            ));
            $sql_in_rs = $sql_in->fetch(PDO::FETCH_ASSOC);

            if ($rs['msg_status'] == 0) {
                $status = "<strong>Unread</strong>";
            } else {
                $status = "<em>Read</em>";
            }
            $list .= '<li>
                        <a href="view_inbox.html" data-id="' . $rs['id'] . '" class="item-link item-content view-msg">
                            <div class="item-inner">
                                <div class="item-title-row">
                                    <div class="item-title">' . user_details($sql_in_rs['sender'], "name") . '</div>
                                    <div class="item-after">' . $status . '</div>
                                </div>
                                <div class="item-subtitle">' . $sql_in_rs['title'] . '</div>
                                <div class="item-text">' . date("F d, y h:i a", $sql_in_rs['date_sent']) . '</div>
                            </div>
                        </a>
                    </li>';

        }

        $list .= "</ul></div>";
    }

    echo $list;
    exit();
}

if(isset($_GET['load_inbox_msg'])){
    $user = $_GET['user'];
    $m_id = $_GET['message_id'];

    $sql = $db->prepare("SELECT * FROM received WHERE id = :m_id and receiver = :me");
    $sql->execute(array(
        'm_id' => $m_id,
        'me' => $user
    ));

    $sql_rs = $sql->fetch(PDO::FETCH_ASSOC);

    $msg_info_rs = msg_info($sql_rs['message_id']);

    $out = array(
        'title' => $msg_info_rs['title'],
        'sender' => user_details($msg_info_rs['sender'],"name"),
        'date_sent' => date("F d, Y h:i a",$msg_info_rs['date_sent']),
        'msg' => $msg_info_rs['msg']
    );

    $sql->closeCursor();

    $up = $db->prepare("UPDATE received SET msg_status = :status WHERE id = :id");
    $up->execute(array('status' => 1,
            'id' => $m_id
        ));
    echo json_encode($out);
    exit;
}


if(isset($_GET['load_sent_msg'])){
    $user = $_GET['user'];
    $m_id = $_GET['message_id'];

    $sql = $db->prepare("SELECT * FROM messages WHERE id = :m_id and sender = :me");
    $sql->execute(array(
        'm_id' => $m_id,
        'me' => $user
    ));

    $sql_rs = $sql->fetch(PDO::FETCH_ASSOC);

    $msg_info_rs = msg_info($sql_rs['id']);

    $out = array(
        'title' => $msg_info_rs['title'],
        'date_sent' => date("F d, Y h:i a",$msg_info_rs['date_sent']),
        'msg' => $msg_info_rs['msg']
    );

    $sql->closeCursor();

    //receiver

    $re = $db->prepare("SELECT receiver FROM received WHERE message_id = :m_id");
    $re->execute(array('m_id' => $m_id));

    $list = "";

    while($re_rs = $re->fetch(PDO::FETCH_ASSOC)){
        $list .=", ". user_details($re_rs['receiver'],"name");
    }

    $list = substr($list,1,(strlen($list) -1));
    $out['receiver'] = $list;
    $re->closeCursor();


    echo json_encode($out);
    exit;
}

function msg_info($id){
    global $db;

    $sql = $db->prepare("SELECT * FROM messages WHERE id = :id");
    $sql->execute(array('id' => $id));

    $rs = $sql->fetch(PDO::FETCH_ASSOC);

    $sql->closeCursor();

    return $rs;
}