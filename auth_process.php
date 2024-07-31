<?php

require_once("models/User.php");
require_once("models/Message.php");
require_once("globals.php");
require_once("db.php");
require_once("dao/UserDAO.php");


$message = new Message($BASE_URL);

$userDao = new userDao($conn, $BASE_URL);
//Verifica o tipo do usuario    

$type = filter_input(INPUT_POST, "type");

// Verificação do tipo de formulário
if ($type === "register") {
    $name = filter_input(INPUT_POST, "name");
    $lastname = filter_input(INPUT_POST, "lastname");
    $email = filter_input(INPUT_POST, "email");
    $password = filter_input(INPUT_POST, "password");
    $confirmpassword = filter_input(INPUT_POST, "confirmpassword");

    // Verificação de dados minimos
    if ($name && $lastname && $email && $password) {

        //Verificar se as senhas batem 
        if ($password === $confirmpassword) {

            // Verficar se o e-mail já esta cadastrado no sistema
            if ($userDao->findByEmail($email) === false) {

                $user = new User();

                // Criação de Token e senha
                $userToken = $user->generateToken();
                $finalPassword = $user->generatePassword($password);

                $user->name = $name;
                $user->lastname = $lastname;
                $user->email = $email;
                $user->password = $finalPassword;
                $user->token = $userToken;

                $auth = true;

                $userDao->create($user, $auth);
            } else {
                $message->setMessage("Usuario já cadastrado, tente outro e-mail", "error", "back");
            }
        } else {

            //Senhas não batem
            $message->setMessage("As senhas não são iguais.", "error", "back");
        }
    } else {

        // Enviar uma mensagem de erro, de dados faltantes
        $message->setMessage("Por favor, preencha todos os campos.", "error", "back");
    }
} else if ($type === "login") {

    $email = filter_input(INPUT_POST, "email");
    $password = filter_input(INPUT_POST, "password");

    // Tenta autenticar usuário
    if ($userDao->authenticateUser($email, $password)) {

        $message->setMessage("Seja bem-vindo", "success", "index.php");

        //redireciona o usuário, caso não conseguir autenticar
    } else {
        $message->setMessage("Usuario e/ou senha incorretos.", "error", "back");
    }
} else {
    $message->setMessage("Informações invalidas.", "error", "index.php");
}
