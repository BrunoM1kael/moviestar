<?php

require_once("models/User.php");
require_once("models/Message.php");
require_once("globals.php");
require_once("db.php");
require_once("dao/UserDAO.php");


$message = new Message($BASE_URL);

$userDao = new UserDao($conn, $BASE_URL);

$type = filter_input(INPUT_POST, "type");

//Atualizar usuário 
if ($type === "update") {

    //resgata dados do usuário
    $userData = $userDao->verifyToken();

    //receber dados do post
    $name = filter_input(INPUT_POST, "name");
    $lastname = filter_input(INPUT_POST, "lastname");
    $email = filter_input(INPUT_POST, "email");
    $bio = filter_input(INPUT_POST, "bio");

    //criar um novo objeto de usuário
    $user = new User();

    $userData->name = $name;
    $userData->lastname = $lastname;
    $userData->email = $email;
    $userData->bio = $bio;

    //upload da imagem 
    if (isset($_FILES["image"]) && !empty($_FILES["image"]["tmp_name"])) {

        $image = $_FILES["image"];
        $imageTypes = ["image/jpeg", "image/jpg", "image/png"];
        $jpgArray = ["image/jpeg", "image/jpg"];

        // Checagem de tipo de imagem
        if (in_array($image["type"], $imageTypes)) {

            // Checar se jpg
            if (in_array($image["type"], $jpgArray)) {

                $imageFile = imagecreatefromjpeg($image["tmp_name"]);

                // Imagem é png
            } else {

                $imageFile = imagecreatefrompng($image["tmp_name"]);
            }

            $imageName = $user->imageGenerateName();

            imagejpeg($imageFile, "./img/users/" . $imageName, 100);

            $userData->image = $imageName;
        } else {

            $message->setMessage("Tipo inválido de imagem, insira png ou jpg!", "error", "back");
        }
    }

    $userDao->update($userData);
    // atualizar senha do usuário
} else if ($type === "changepassword") {

    $password = filter_input(INPUT_POST, "password");
    $confirmpassword = filter_input(INPUT_POST, "confirmpassword");
    $id = filter_input(INPUT_POST, "id");

    if ($password == $confirmpassword) {

        $user = new User();

        $finalPassword = $user->generatePassword($password);

        $user->password = $finalPassword;
        $user->id = $id;

        $userDao->changePasswords($user);
    } else {
        $message->setMessage("As senhas não são iguais", "error", "back");
    }
} else {
    $message->setMessage("Informações invalidas.", "error", "index.php");
}
