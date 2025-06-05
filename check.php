<?php
error_reporting(0);
session_start();
$host="localhost";
$user="root";
$password="";
$db="schoolproject";
$conn=mysqli_connect($host,$user,$password,$db);

if($conn==false){
    die("connection error");
}
if($_SERVER["REQUEST_METHOD"]=="POST"){
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $sql="SELECT * FROM personne where email='".$email."' AND password='".$password."'" ;
    $result=mysqli_query($conn,$sql);
    $row=mysqli_fetch_array($result);
    if($row["rôle"]=="student"){

        $_SESSION['name']=$name;
        $_SESSION["rôle"]="student";
        header("location:student.php");
    }
    elseif($row["rôle"]=="admin"){
        $_SESSION['name']=$name;
        $_SESSION["rôle"]="admin";
        header("location:admin.php");

    }
    elseif($row["rôle"]=="teacher"){
        $_SESSION['name']=$name;
        $_SESSION["rôle"]="teacher";
        header("location:teacher.php");

    }
    else {
        $_SESSION['loginMessage'] = "Email ou mot de passe incorrect !";
        header("Location: connect.php");
        exit();
    }

}





?>